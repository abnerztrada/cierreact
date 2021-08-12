<?php
namespace tool_cierre\models;
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 *
 */
class correo
{

  public function __construct()
  {
    // code...
  }

  public function correo_envio(){
      //Query de curso con sus fechas
      $query = "Select c.id, c.shortname, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechafin,
        DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha_cierre
        FROM (select @s:=0) as s, mdl_course c
        INNER JOIN mdl_customfield_data cd ON cd.instanceid = c.id
        where c.visible = 1 and cd.fieldid = 43 and c.id = 241";

      global $DB;
      $result = $DB->get_records_sql($query);

      //Url 
      $url = 'https://calidad.laucmi.telefonicaed.pe/local/rep2/report.php?id=';
      $url2= 'https://calidad.laucmi.telefonicaed.pe/local/rep/report.php?id=';

      foreach ($result as $it) {
        $urltemp = $url.$it->id;
        $urltemp2 = $url2.$it->id; 
        
        //Fecha cierre envio
        $fecha_cierre = $it->fecha_cierre; 

        //fechafin 
        $fechaFin = $it->fechafin;
        //Fecha actual
        date_default_timezone_set("America/Guatemala");
        $fechaAct = date("d/m/Y H:i");


        // Query de stakholder a quien se le envia el correo
        $query2 = "SELECT  @s:=@s + 1 id_auto, concat(u.firstname,' ', u.lastname) as nombre, u.email, c.shortname, c.fullname, 
                  asg.roleid, asg.userid, r.shortname as stakholder FROM
                  (select @s:=0) as s,
                  mdl_user u
                  INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
                  INNER JOIN mdl_context as con on asg.contextid = con.id
                  INNER JOIN mdl_course c on con.instanceid = c.id
                  INNER JOIN mdl_role r on asg.roleid = r.id
                  where c.shortname = '$it->shortname' and r.shortname = 'stakeholder'";
        $result2 = $DB->get_records_sql($query2);

        echo '<pre>';
          print_r($result2);
        echo '</pre>';
        echo $fecha_cierre; 

        // // solamente se saco el conteo de los estudiantes
        // $query3 = "SELECT   @s:=@s + 1 id_auto, c.id, c.fullname, COUNT(r.shortname) as estudiantes FROM
        //           (select @s:=0) as s,
        //           mdl_user u
        //           INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
        //           INNER JOIN mdl_context as con on asg.contextid = con.id
        //           INNER JOIN mdl_course c on con.instanceid = c.id
        //           INNER JOIN mdl_role r on asg.roleid = r.id
        //           where c.id = '$it->id' and r.shortname = 'student'";
        // $result3 = $DB->get_records_sql($query3);

        foreach ($result2 as $it2) {
          $body = $urltemp;
          $body2 = $urltemp2; 

          //Esta es de moodle $emailuser->email
          $emailuser->email = $it2->email;
          $emailuser->id = -99;
          $subject = $it2->fullname;
          $emailuser->maildisplay = true;
          $emailuser->mailformat = 1;
          $nombre = $it2->nombre;

          //Imagen 
          $String ="<img src='http://54.161.158.96/local/img/banner.jpg'";  

          //Texto para el cierre
          $string1 = ""; 
          $string1 .= $String."\n";
          $string1 .= "<br>"; 
          $string2 .= "<br>"; 
          $string1 .= "<div style='color: orange; font-size: 18px; font-family: Century Gothic;'> $nombre </div>";
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Los resultados del curso <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $subject, </span>  el cual finalizó <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechaFin, </span> los encontrarás en los siguientes enlaces: </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Resultado de satisfacción $body </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Estatus de cumplimiento de los participantes $body2 </div>";
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Cualquier duda o comentario puedes escribirnos a cmi-laucmi@somoscmi.com \n </div>";
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Atentamente, \n </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> laUcmi \n </div>";
          
          //Comparaciones de fechas para el envio del correo electronico
            if($fechaAct == $fecha_cierre ){
                $email = email_to_user($emailuser,'laUcmi','Resultados del cierre del curso '.$subject, $string1);
                echo "Correo enviado";
              }else{
                echo "Correo no envieado";
              }
          }
        }
      }
    }
?>
