<?php
require_once (__DIR__.'/../Modelo/GeneralFunctions.php');
require_once (__DIR__.'/../Modelo/Persona.php');
require_once (__DIR__.'/../Modelo/Especialidad.php');

if(!empty($_GET['action'])){
    PersonaController::main($_GET['action']);
}else{
    echo "No se encontro ninguna accion...";
}

class PersonaController{

    static function main($action){
        if ($action == "crear"){
            PersonaController::crear();
        }else if ($action == "editar"){
            PersonaController::editar();
        }else if ($action == "buscarID"){
            PersonaController::buscarID($_REQUEST['idPersona']);
        }else if ($action == "ActivarPersona"){
            PersonaController::ActivarPersona();
        }else if ($action == "InactivarPersona"){
            PersonaController::InactivarPersona();
        }else if ($action == "asociarEspecialidad"){
            PersonaController::asociarEspecialidad();
        }else if ($action == "eliminarEspecialidad"){
            PersonaController::eliminarEspecialidad();
        }
    }

    static public function crear (){
        try {
            $arrayPersona = array();
            $arrayPersona['Tipo_Documento'] = $_POST['Tipo_Documento'];
            $arrayPersona['Documento'] = $_POST['Documento'];
            $arrayPersona['Nombres'] = $_POST['Nombres'];
            $arrayPersona['Apellidos'] = $_POST['Apellidos'];
            $arrayPersona['Telefono'] = $_POST['Telefono'];
            $arrayPersona['Direccion'] = $_POST['Direccion'];
            $arrayPersona['Correo'] = $_POST['Correo'];
            $arrayPersona['NRP'] = (!empty($_POST['NRP']) ? $_POST['NRP'] : NULL);
            $arrayPersona['Profesion'] = (!empty($_POST['NRP']) ? $_POST['Profesion'] : NULL);
            $arrayPersona['Usuario'] = $_POST['Usuario'];
            $arrayPersona['Contrasena'] = $_POST['Contrasena'];
            $arrayPersona['Tipo_Usuario'] = $_POST['Tipo_Usuario'];
            $arrayPersona['Observaciones'] = (!empty($_POST['Observaciones']) ? $_POST['Observaciones'] : NULL);
            if($arrayPersona['Tipo_Usuario'] == "Medico" AND (!empty($_POST['relEspecialidades']))){
                $arrayPersona['relEspecialidades'] = Especialidad::buscarForId($_POST['relEspecialidades']);
            }
            $arrayPersona['Estado'] = 'Activo';

            //Subir el archivo
            if (!empty($_FILES['Foto']) && ($_FILES['Foto']["name"] != "" )){
                $NameFile = GeneralFunctions::SubirArchivo($_FILES['Foto'],'../Vista/filesUploaded/');
                if ($NameFile != false){
                    $arrayPersona['Foto'] = $NameFile;
                }else{
                    throw new Exception('La imagen no se pudo subir.');
                }
            }

            $Persona = new Persona ($arrayPersona);
            $Persona->insertar();
            header("Location: ../Vista/modules/persona/create.php?respuesta=correcto");
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/create.php?respuesta=error&mensaje=".$e->getMessage());
        }
    }

    public static function personaIsInArray($idPersona, $ArrPersonas){
        if(count($ArrPersonas) > 0){
            foreach ($ArrPersonas as $Persona){
                if($Persona->getIdPersona() == $idPersona){
                    return true;
                }
            }
        }
        return false;
    }

    static public function selectPersona ($isMultiple=false,
                                          $isRequired=true,
                                          $id="idConsultorio",
                                          $nombre="idConsultorio",
                                          $defaultValue="",
                                          $class="",
                                          $where="",
                                          $arrExcluir = array()){
        $arrPersonas = array();
        if($where != ""){
            $base = "SELECT * FROM persona WHERE ";
            $arrPersonas = Persona::buscar($base.$where);
        }else{
            $arrPersonas = Persona::getAll();
        }

        $htmlSelect = "<select ".(($isMultiple) ? "multiple" : "")." ".(($isRequired) ? "required" : "")." id= '".$id."' name='".$nombre."' class='".$class."'>";
        $htmlSelect .= "<option value='' >Seleccione</option>";
        if(count($arrPersonas) > 0){
            foreach ($arrPersonas as $persona)
                if (!PersonaController::personaIsInArray($persona->getIdPersona(),$arrExcluir))
                    $htmlSelect .= "<option ".(($persona != "") ? (($defaultValue == $persona->getIdPersona()) ? "selected" : "" ) : "")." value='".$persona->getIdPersona()."'>".$persona->getNombres()." ".$persona->getApellidos()."</option>";
        }
        $htmlSelect .= "</select>";
        return $htmlSelect;
    }


    static public function editar (){
        try {
            $arrayPersona = array();
            $arrayPersona['Tipo_Documento'] = $_POST['Tipo_Documento'];
            $arrayPersona['Documento'] = $_POST['Documento'];
            $arrayPersona['Nombres'] = $_POST['Nombres'];
            $arrayPersona['Apellidos'] = $_POST['Apellidos'];
            $arrayPersona['Telefono'] = $_POST['Telefono'];
            $arrayPersona['Direccion'] = $_POST['Direccion'];
            $arrayPersona['Correo'] = $_POST['Correo'];
            $arrayPersona['NRP'] = (!empty($_POST['NRP']) ? $_POST['NRP'] : NULL);
            $arrayPersona['Profesion'] = (!empty($_POST['NRP']) ? $_POST['Profesion'] : NULL);
            $arrayPersona['Usuario'] = $_POST['Usuario'];
            $arrayPersona['Contrasena'] = $_POST['Contrasena'];
            $arrayPersona['Tipo_Usuario'] = $_POST['Tipo_Usuario'];
            $arrayPersona['Observaciones'] = (!empty($_POST['Observaciones']) ? $_POST['Observaciones'] : NULL);
            $arrayPersona['Estado'] = $_POST['Estado'];
            $arrayPersona['idPersona'] = $_POST['idPersona'];

            //Subir el archivo
            if (!empty($_FILES['Foto']) && ($_FILES['Foto']["name"] != "" )){
                var_dump($_FILES['Foto']);
                $NameFile = GeneralFunctions::SubirArchivo($_FILES['Foto'],'../Vista/filesUploaded/');
                if ($NameFile != false){
                    $arrayPersona['Foto'] = $NameFile;
                }else{
                    throw new Exception('La imagen no se pudo subir.');
                }
            }else{
                $persona = PersonaController::buscarID($arrayPersona['idPersona']);
                $arrayPersona['Foto'] = $persona->getFoto();
            }

            $person = new Persona($arrayPersona);
            $person->editar();

            header("Location: ../Vista/modules/persona/view.php?id=".$person->getIdPersona()."&respuesta=correcto");
        } catch (Exception $e) {
            var_dump($e);
            //header("Location: ../Vista/modules/persona/edit.php?respuesta=error");
        }
    }

    static public function ActivarPersona (){
        try {
            $ObjPersona = Persona::buscarForId($_GET['IdPersona']);
            $ObjPersona->setEstado("Activo");
            $ObjPersona->editar();
            header("Location: ../Vista/modules/persona/manager.php");
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

    static public function InactivarPersona (){
        try {
            $ObjPersona = Persona::buscarForId($_GET['IdPersona']);
            $ObjPersona->setEstado("Inactivo");
            $ObjPersona->editar();
            header("Location: ../Vista/modules/persona/manager.php");
        } catch (Exception $e) {
            var_dump($e);
            //header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

    static public function buscarID ($id){
        try {
            return Persona::buscarForId($id);
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

    public function buscarAll (){
        try {
            return Persona::getAll();
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

    public function buscar ($Query){
        try {
            return Persona::buscar($Query);
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

    static public function asociarEspecialidad (){
        try {
            $Persona = new Persona();
            $Persona->asociarEspecialidad($_POST['Persona'],$_POST['Especialidad']);
            header("Location: ../Vista/modules/persona/managerSpeciality.php?respuesta=correcto&id=".$_POST['Persona']);
        } catch (Exception $e) {
            header("Location: ../Vista/modules/persona/managerSpeciality.php?respuesta=error&mensaje=".$e->getMessage());
        }
    }

    static public function eliminarEspecialidad (){
        try {
            $ObjPersona = new Persona();
            if(!empty($_GET['Persona']) && !empty($_GET['Especialidad'])){
                $ObjPersona->eliminarEspecialidad($_GET['Persona'],$_GET['Especialidad']);
            }else{
                throw new Exception('No se recibio la informacion necesaria.');
            }
            header("Location: ../Vista/modules/persona/managerSpeciality.php?id=".$_GET['Persona']);
        } catch (Exception $e) {
            var_dump($e);
            //header("Location: ../Vista/modules/persona/manager.php?respuesta=error");
        }
    }

}