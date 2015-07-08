<?php
/**
 * Created by PhpStorm.
 * User: gmartin
 * Date: 19/06/2015
 * Time: 10:13 AM
 */
include("model/class.user.php");
include("model/class.phpmailer.php");
include("model/class.toner.php");
include("model/class.impresora.php");


class mvc {

    private $SMTP_HOST = 'mail.bavosi.com.ar';
    private $URL_WEB_APP = 'http://localhost/insumos';
    private $NAME_WEB_APP = 'Insumos';
    private $EMAIL_WEB_APP = 'insumos@bavosi.com.ar';
    private $EMAIL_WEB_APP_FROM_NAME = 'Insumos WebApp';
    private $EMAIL_SUP_IT = 'sistemas@bavosi.com.ar';
    private $VERSION = '1.0.0.0';

    public $user;

    function __construct(){ $this->user = new User(); }
    function __destruct() { unset($this); }

    /**
     * @param $user_name
     * @param $password
     * Hace el login en la aplicacion con las credenciales de usuario, de ser correcta crea el objeto session
     * De ser incorrectos vuelve a cargar el formulario de login informando el error
     */
    public function login_session($user_name,$password){
        if($this->user->check_credentials($user_name,$password)){
            session_start();
            $_SESSION["authenticate"] = true;
            $_SESSION["user_name"] = $this->user->user_name;
            $_SESSION["name"] = $this->user->name;
            $_SESSION["email"] = $this->user->email;
            $_SESSION["is_admin"] = $this->user->is_admin;
            $this->principal();
        }else{
            $this->login("Nombre de Usuario o Contrase&ntilde;a incorrecta");
        }
    }


    /**
     * @return bool
     * Autentica la sesion actual, de no existir carga el formulario de login
     */
    public function authenticate(){
        //Reanudamos la sesión
        @session_start();

        if(!$_SESSION["authenticate"]) {
            return false;
            exit();
        }
        return true;
    }

    /**
     * Destruye la sesion actual
     */
    public function exit_session(){
        session_start();
        session_destroy();
        $this->login();
    }


    private function send_email($recipient="",$recipients=array(),$subjet="",$body=""){
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->Host = $this->SMTP_HOST;
        $mail->From = $this->EMAIL_WEB_APP;
        $mail->FromName = $this->EMAIL_WEB_APP_FROM_NAME;
        $mail->Subject = $subjet;
        if($recipient!=""){
            $mail->AddAddress($recipient);
        }else{
            foreach($recipients as $to){ $mail->AddAddress($to); }
        }
        $mail->AddBCC($this->EMAIL_SUP_IT);
        $mail->Body = $body;
        return $mail->Send();
    }


    /**
     * @param $page
     * @return string
     * METODO QUE CARGA UNA PAGINA DE LA SECCION VIEW Y LA MANTIENE EN MEMORIA
     */
    private function load_page($page){
        return file_get_contents($page);
    }

    /**
     * @param $in
     * @param $out
     * @param $pagina
     * @return mixed
     * PARSEA LA PAGINA CON LOS NUEVOS DATOS ANTES DE MOSTRARLA AL USUARIO
     */
    private function replace_content($in, $out,$pagina){
        return preg_replace($in, $out, $pagina);
    }

    /**
     * @param $html
     * METODO QUE ESCRIBE EL CODIGO PARA QUE SEA VISTO POR EL USUARIO
     */
    private function view_page($html){
        echo $html;
    }

    /**
     * @param string $title -> titulo para la ventana de la web
     * @return mixed|string $pagina -> contiene el codigo HTML final
     * METODO QUE CARGA LAS PARTES PRINCIPALES DE LA PAGINA WEB
     */
    private function load_template($title='Sin Titulo'){
        $pagina = $this->load_page('view/default/page.html');
        $header = $this->load_page('view/default/sections/header.html');
        $footer = $this->load_page('view/default/sections/footer.html');
        $pagina = $this->replace_content('/\{title\}/ms' ,$title , $pagina);
        $pagina = $this->replace_content('/\{header\}/ms' ,$header , $pagina);
        $pagina = $this->replace_content('/\{footer\}/ms' ,$footer , $pagina);
        $pagina = $this->replace_content('/\{version\}/ms' ,$this->VERSION, $pagina);
        return $pagina;
    }

    function sign_up($error="",$mensaje=""){
        $pagina=$this->load_template('Pagina Principal');
        $html = $this->load_page('view/default/modules/registrarse.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        if($error != ""){
            $alert = $this->load_page('view/default/modules/alert_error.html');
            $pagina = $this->replace_content('/\{alert\}/ms' ,$alert , $pagina);
            $pagina = $this->replace_content('/\{alertDetail\}/ms' ,$error , $pagina);
        }else if($mensaje != ""){
            $alert = $this->load_page('view/default/modules/alert_ok.html');
            $pagina = $this->replace_content('/\{alert\}/ms' ,$alert , $pagina);
            $pagina = $this->replace_content('/\{alertDetail\}/ms' ,$mensaje , $pagina);
        }else{
            $pagina = $this->replace_content('/\{alert\}/ms' ,"", $pagina);
        }
        $this->view_page($pagina);
    }

    function login($error=""){
        $pagina=$this->load_template('Pagina Principal');
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        $html = $this->load_page('view/default/modules/login.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        if($error != ""){
            $alert = $this->load_page('view/default/modules/alert_error.html');
            $pagina = $this->replace_content('/\{alert\}/ms' ,$alert , $pagina);
            $pagina = $this->replace_content('/\{alertDetail\}/ms' ,$error , $pagina);
        }else{
            $pagina = $this->replace_content('/\{alert\}/ms' ,"", $pagina);
        }
        $this->view_page($pagina);
    }

    function new_user($user_data=array()){

        $this->user->set($user_data);

        if($this->user->id != ""){
            $body = $this->load_page('view/default/modules/mail_alta_usuario.html');
            $body = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$body);
            $body = $this->replace_content('/\{url\}/ms' ,$this->URL_WEB_APP,$body);
            $body = $this->replace_content('/\{user_name\}/ms' ,$this->user->user_name,$body);
            $body = $this->replace_content('/\{password\}/ms',$this->user->password,$body);
            $this->send_email($this->user->email,"","Alta de Usuario",$body);
            return true;
        }else{
            return false;
        }
    }

    function principal(){
        $this->authenticate();
        $pagina=$this->load_template('Pagina Principal');
        $html = $this->load_page('view/default/modules/principal.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        $this->view_page($pagina);
    }

    function tonerConsulta(){
        $this->authenticate();
        $pagina=$this->load_template('Insumos Impresoras');
        $html = $this->load_page('view/default/modules/tonerConsulta.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        $toner = new Toner();
        $renglones = $toner->listar();
        $tr_html = "";
        foreach($renglones as $fila){
            $tr = $this->load_page('view/default/modules/tonerConsultaRenglones.html');
            $tr = $this->replace_content('/\{Toner\}/ms' ,$fila['Toner'] , $tr);
            $tr = $this->replace_content('/\{Categoria\}/ms' ,$fila['Categoria'] , $tr);
            $tr = $this->replace_content('/\{Impresoras\}/ms' ,$fila['Impresoras'] , $tr);
            $tr_html = $tr_html.$tr;

        }
        $pagina = $this->replace_content('/\{Tabla\}/ms' ,$tr_html , $pagina);
        $this->view_page($pagina);
    }

    function retiroConsulta(){
        $this->authenticate();
        $pagina=$this->load_template('Insumos Impresoras');
        $html = $this->load_page('view/default/modules/retiroConsulta.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        $this->view_page($pagina);
    }

    function impresoraConsulta(){
        $this->authenticate();
        $pagina=$this->load_template('Insumos Impresoras');
        $html = $this->load_page('view/default/modules/impresoraConsulta.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $pagina = $this->replace_content('/\{appName\}/ms' ,$this->NAME_WEB_APP,$pagina);
        $impresora = new Impresora();
        $renglones = $impresora->listar();
        $tr_html = "";
        foreach($renglones as $fila){
            $tr = $this->load_page('view/default/modules/impresoraConsultaRenglones.html');
            $tr = $this->replace_content('/\{Impresoras\}/ms' ,$fila['MarcaModelo'] , $tr);
            $tr = $this->replace_content('/\{Sector\}/ms' ,$fila['Sector'] , $tr);
            $tr = $this->replace_content('/\{Toner\}/ms' ,$fila['Toners'] , $tr);
            $tr_html = $tr_html.$tr;
        }
        $pagina = $this->replace_content('/\{Tabla\}/ms' ,$tr_html , $pagina);

        $this->view_page($pagina);
    }

    function ayuda(){
        $this->authenticate();
        $pagina=$this->load_template('Ayuda');
        $html = $this->load_page('view/default/modules/ayuda.html');
        $pagina = $this->replace_content('/\{content\}/ms' ,$html , $pagina);
        $this->view_page($pagina);
    }

}