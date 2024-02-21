<?php

namespace App\Http\Controllers;

use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Date;
use App\Models\Evolution;
use App\Models\Interview;
use App\Models\People;
use App\Models\Exam;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\Surgery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Spipu\Html2Pdf\Html2Pdf;

class PdfController extends Controller {
    public function getDataOrders($id) {
        $pdfname = "";
        $packs = Order::select(
            'Orders.DateID',
            'Dates.Date as Date',
            'Dates.Time as Time',
            'Peoples.Name as PeopleName',
            'Peoples.Lastname as PeopleLastname',
            'Diagnosis.Name as DiagnosisName',
            'Peoples.CardCode as PeopleCardCode',
            'Peoples.Birthday as PeopleBirthday'
        )
        ->leftJoin('Dates', 'Dates.DateID', '=', 'Orders.DateID')
        ->leftJoin('Peoples', 'Peoples.PeopleID', '=', 'Dates.PeopleID')
        ->leftJoin('Diagnosis', 'Diagnosis.DiagnosisID', '=', 'Dates.DiagnosisID')
        ->where("Orders.DateID",$id)
        ->groupBy(
            "Orders.DateID",
            "Dates.Date","Dates.Time",
            'Peoples.Name',
            'Peoples.Lastname',
            'Diagnosis.Name',
            'Peoples.CardCode',
            'Peoples.Birthday'
        )
        ->orderBy("Orders.DateID","DESC")
        ->get();
        foreach ($packs as $pack) {
            $exams = Order::select(
                'Dates.Date as Date',
                'Dates.Time as Time',
                'Exams.ExamTypeID',
                'Exams.Name as ExamName',
                'ExamTypes.Name as ExamTypeName'
            )
            ->leftJoin('Dates', 'Dates.DateID', '=', 'Orders.DateID')
            ->join('Exams', 'Exams.ExamID', '=', 'Orders.ExamID')
            ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
            ->where('Orders.DateID', $id)
            ->orderBy('ExamTypes.Name','ASC')
            ->groupBy(
                'Dates.Date',
                'Dates.Time',
                'Exams.ExamTypeID',
                'Exams.Name',
                'ExamTypes.Name'
            )
            ->get();

            $rows  = [];
            $acc = [ "ExamTypeName" => "", "ExamTypeID" => "", "Exams" => [] ];
            $lastExamTypeID = "";
            foreach ($exams as $ex) {
                if ($lastExamTypeID!="" && $ex->ExamTypeID != $lastExamTypeID) {
                    $rows[] = $acc;
                    $acc = [ "ExamTypeName" => "", "ExamTypeID" => "", "Exams" => [] ];
                }
                $acc["ExamTypeID"] = $ex->ExamTypeID;
                $acc["ExamTypeName"] = $ex->ExamTypeName;
                $acc["Exams"][] = $ex->ExamName;

                $lastExamTypeID = $ex->ExamTypeID;
            }
            if (count($acc)>0) {
                $rows[] = $acc;
            }
            $opt = [
                "DateID" => $pack->DateID,
                "Date" => $pack->Date,
                "Time" => $pack->Time,
                "data" => $rows,
                "Name" => "",
                "Diagnosis" => "",
                "CardCode" => $pack->PeopleCardCode,
                "Birthday" => $pack->PeopleBirthday
            ];
            if ($pack->PeopleName != "" && $pack->PeopleLastname != "") {
                $opt["Name"] = $pack->PeopleName." ".$pack->PeopleLastname;
                $pdfname = $pack->PeopleName." ".$pack->PeopleLastname;
            }
            if ($pack->DiagnosisName != "") {
                $opt["Diagnosis"] = $pack->DiagnosisName;
            }
            $output[] = $opt;            
        } 
        $content="";
        foreach ($output as $dates) {
            foreach ($dates["data"] as $datas) {
                $content.= '<h4>Rp</h4><b>'.$datas["ExamTypeName"].'</b><br><br>';
                foreach ($datas["Exams"] as $exm) {
                    $content .= "- ".$exm."<br>";
                }
                $content .= '<br /><br /></page>';   
            }
        }

        $ccc = $dates["CardCode"];
        $ccc = str_replace(["-","."],["",""], $ccc);
        $rut = number_format( substr ( $ccc, 0 , -1 ) , 0, "", ".") . '-' . substr ( $ccc, strlen($ccc) -1 , 1 );
        if ($dates["Birthday"]=="") {
            $dates["Birthday"] = date("Y-m-d H:i:s");
        }
        $fecha_nac = new \DateTime(date('Y/m/d',strtotime($dates["Birthday"]))); 
        $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
        $edad = date_diff($fecha_hoy,$fecha_nac); 

        $content="
        <b>NOMBRE:</b> ".ucwords(mb_strtolower($dates["Name"],"utf-8"))."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EDAD:</b> ".$edad->format("%Y")." años"."<br />
        <b>RUT:</b> ".$rut."<br /> 
        <b>DIAGNÓSTICO:</b> ".$dates["Diagnosis"]."<br />   
        ".$content;

        $path = dirname(__FILE__)."/../../../resources/views/generic.html";
        if (!file_exists($path)) {
            throw new \Exception("not found template ".$path);
        }   
        $fp = file_get_contents($path);
        $fp = str_replace("{{html}}",$content,$fp);
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);

        //210x279 
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($fp); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();

    } 
    public function getPeople($id) {
        $people = People::select('Peoples.*','Groups.Name as GroupName','Healths.Name as HealthName','Status.Name as StatusName')
                ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
                ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID')
                ->where("PeopleID", $id);    
        
        $people = $people->first();
        $pdfname = $people->Name." ".$people->Lastname;
        $ant = Anthropometry::where("PeopleID", $id)->orderBy("AnthropometryID","DESC")->first();
        $evolutions =  Evolution::select("Evolutions.*", "Users.Name as CreatedByName")
                            ->join("Users","Users.UserID","=","Evolutions.CreatedUserID")
                            ->where("Evolutions.PeopleID", $id)
                            ->get();

        if ($people->Birthday=="") {
            $people->Birthday = date("Y-m-d H:i:s");
        }
        $fecha_nac = new \DateTime(date('Y/m/d',strtotime($people->Birthday))); 
        $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
        $edad = date_diff($fecha_hoy,$fecha_nac); 
        $imc = 0;

        $edv = DB::select("
        SELECT		E.ExamID,  ET.Name ExamTypeName, E.Name, ED.ExamDataType, ED.Name ExamDataName, EDV.Value 
        FROM		Exams as E 
        INNER JOIN	ExamTypes ET ON ET.ExamTypeID = E.ExamTypeID
        INNER JOIN	ExamDatas ED ON ED.ExamID = E.ExamID 
        INNER JOIN	ExamDataValues EDV ON EDV.ExamDataID = ED.ExamDataID 
        INNER JOIN  Orders O ON O.PeopleID = '".$id."' AND O.OrderID = EDV.OrderID 
        WHERE		E.Active = 1 
        ORDER BY	ET.Name ASC, EDV.ExamDataValueID DESC
        ");
        $edv = json_decode(json_encode($edv), true);
        $results = [];
        foreach ($edv as $rr) {
            if (!isset($results[$rr["ExamTypeName"]][$rr["ExamDataName"]])) { // Only newest result
                if ($rr["ExamDataType"]=="boolean") {
                    if ($rr["Value"]=="1") {
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "OK";
                    }
                    else {
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "NO-OK";
                    }
                }
                else if ($rr["ExamDataType"]=="text") { 
                    $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = $rr["Value"];
                } 
                else if ($rr["ExamDataType"]=="number") { 
                    $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = round($rr["Value"],2);
                }
            }
        }

        $surgerys = Date::select(
            "Dates.*",
            "Surgerys.Name as SurgeryName",
            "Diagnosis.Name as DiagnosisName"
          )
            ->join("Surgerys","Surgerys.SurgeryID","=","Dates.SurgeryID")
            ->leftJoin("Diagnosis","Diagnosis.DiagnosisID","=","Dates.DiagnosisID")
            ->where("Dates.PeopleID", $id)
            ->where("Dates.SurgeryID",">",0)
            ->orderBy("Dates.DateID","DESC")
            ->get();
        /*
            <tr><td class="width-120"><b>Temp. (C&deg;):</b></td><td>'.number_format($ant->Temperature,1,",",".").'</td></tr>
            <tr><td class="width-120"><b>Presión:</b></td><td>'.number_format($ant->Sistolic,1,",",".").' / '.number_format($ant->Diastolic,1,",",".").'</td></tr>
        */
        $content= '
        <table width="100%">
          <tr>
            <td class="width-330">
                <h5>Información</h5>
                <table width="100%" valign="top">
                <tr><td class="width-120"><b>Nombre:</b></td><td>'.ucwords(mb_strtolower($people->Name.' '.$people->Lastname,"utf-8")).'</td></tr>
                <tr><td class="width-120"><b>RUT:</b></td><td>'.$people->CardCode.'</td></tr>
                <tr><td class="width-120"><b>Edad:</b></td><td>'.$edad->format('%Y').' años y '.$edad->format('%m').' meses y '.$edad->format('%m').' días</td></tr>
                <tr><td class="width-120"><b>Fecha Nac.:</b></td><td>'.date("d/m/Y", strtotime($people->Birthday)).'</td></tr>
                <tr><td class="width-120"><b>Email:</b></td><td>'.$people->Email.'</td></tr>
                <tr><td class="width-120"><b>Teléfono:</b></td><td>'.$people->Phone.' '.$people->Phone2.'</td></tr>
                <tr><td class="width-120"><b>Dirección:</b></td><td>'.$people->Address.' '.$people->City.'</td></tr>
                <tr><td class="width-120"><b>Profesión:</b></td><td>'.$people->Profession.'</td></tr>
                <tr><td class="width-120"><b>Previsión:</b></td><td>'.$people->HealthName.'</td></tr>
                <tr><td class="width-120"><b>Estado ppto:</b></td><td>'.$people->BudgetStatus.'</td></tr>
                <tr><td class="width-120"><b>Lugar ppto:</b></td><td>'.$people->BudgetPlace.'</td></tr>
                </table> 
            </td>
            <td class="width-80"></td>
            <td class="width-330" valign="top">
            <h5>Datos clínicos</h5>
            <table width="100%">
            <tr><td class="width-120"><b>Peso (kg):</b></td><td>'.($ant ? number_format($ant->Weight,1,",",".") : '').'</td></tr>
            <tr><td class="width-120"><b>Altura (cm):</b></td><td>'.($ant ? number_format($ant->Height,0,",",".").'</td></tr>
            <tr><td class="width-120"><b>IMC:</b></td><td>'.number_format($imc,1,",",".") : '').'</td></tr>
            <tr><td class="width-120"><b>Médicos:</b></td><td>'.($ant ? $ant->AntMedical : '').'</td></tr>
            <tr><td class="width-120"><b>Alergias:</b></td><td>'.($ant ? $ant->AntAllergy : '').'</td></tr>
            <tr><td class="width-120"><b>Quirúrgicos:</b></td><td>'.($ant ? $ant->AntSurgical : '').'</td></tr>
            <tr><td class="width-120"><b>Fármacos:</b></td><td>'.($ant ? $ant->AntDrugs : '').'</td></tr>
            </table>
            </td>
          </tr>
        </table>
        <table width="100%">
          <tr>
            <td class="width-330" valign="top">
                <h5>Evoluciones:</h5>
                ';
                foreach ($evolutions as $ev) {
                    $content .= '<b>'.date("d/m/Y H:i",strtotime($ev->CreatedAt)).' ('.$ev->CreatedByName.'):</b> '.$ev->Description.'<br />';
                }
                $content .= '
                '.(count($evolutions) > 0 ? '' : 'No se han ingresado evoluciones').'
            </td>
            <td class="width-80">

            </td>
            <td class="width-330">
                <h5>Cirug&iacute;a/Diagnóstico:</h5>
                '.(count($surgerys) > 0 ? '' : 'No se ha iniciado el proceso de cirugia').'
            </td>
          </tr>
        </table>
        <hr />   
        <table width="100%">
          <tr>
            <td class="width-740" valign="top">
            ';
            foreach ($results as $type=>$d) {
                $content .= "<h5>".$type."</h5>";
                foreach ($d as $field=>$val) {
                    $content .= "<b>".$field.":</b> ".$val."<br>";
                }
            }
            $content .= '
            </td>
          </tr>
        </table><br /><br />';   

        $path = dirname(__FILE__)."/../../../resources/views/generic.html";
        if (!file_exists($path)) {
            throw new \Exception("not found template ".$path);
        }
        $fp = file_get_contents($path);
        $fp = str_replace("{{html}}",$content,$fp);
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);

        //210x279
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($fp); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();
    } 
    public function getRecipes($id) {
        $pdfname = "";
        $packs = Recipe::select(
            'Recipes.DateID',
            'Dates.Date as Date',
            'Dates.Time as Time',
            'Peoples.Name as PeopleName',
            'Peoples.Lastname as PeopleLastname',
            'Diagnosis.Name as DiagnosisName',
            'Peoples.CardCode as PeopleCardCode',
            'Peoples.Birthday as Birthday'
        )
        ->leftJoin('Dates', 'Dates.DateID', '=', 'Recipes.DateID')
        ->leftJoin('Peoples', 'Peoples.PeopleID', '=', 'Dates.PeopleID')
        ->leftJoin('Diagnosis', 'Diagnosis.DiagnosisID', '=', 'Dates.DiagnosisID')
        ->where("Recipes.DateID",$id)
        ->groupBy(
            "Recipes.DateID",
            "Dates.Date","Dates.Time",
            'Peoples.Name',
            'Peoples.Lastname',
            'Diagnosis.Name',
            'Peoples.CardCode',
            'Peoples.Birthday'
        )
        ->orderBy("Recipes.DateID","DESC")
        ->get();

        $recipes = Recipe::select(
            'Dates.Date as Date',
            'Dates.Time as Time',
            'Medicines.Name',
            'Recipes.*'
        )
        ->leftJoin('Dates', 'Dates.DateID', '=', 'Recipes.DateID')
        ->join('Medicines', 'Medicines.MedicineID', '=', 'Recipes.MedicineID')
        ->where('Recipes.DateID', $id)
        ->orderBy('Medicines.Name','ASC')
        ->get();

        $opt = [
            "DateID" => $packs[0]->DateID,
            "Date" => $packs[0]->Date,
            "Time" => $packs[0]->Time,
            "data" => $recipes, 
            "CardCode" => $packs[0]->PeopleCardCode
        ];
        $opt["Name"] = "";
        if ($packs[0]->PeopleName != "" && $packs[0]->PeopleLastname != "") {
            $opt["Name"] = ucwords(mb_strtolower($packs[0]->PeopleName." ".$packs[0]->PeopleLastname,"utf-8"));
            $pdfname = ucwords(mb_strtolower($packs[0]->PeopleName." ".$packs[0]->PeopleLastname,"utf-8"));
        }
        $opt["Diagnosis"] = "";
        if ($packs[0]->DiagnosisName != "") {
            $opt["Diagnosis"] = $packs[0]->DiagnosisName;
        }
        $opt["Birthday"] = "";
        if ($packs[0]->Birthday != "") {
            $opt["Birthday"] = $packs[0]->Birthday;
        }

        $ccc = $opt["CardCode"];
        $ccc = str_replace(["-","."],["",""], $ccc);
        $rut = number_format( substr ( $ccc, 0 , -1 ) , 0, "", ".") . '-' . substr ( $ccc, strlen($ccc) -1 , 1 );
        if ($opt["Birthday"]=="") {
            $opt["Birthday"] = date("Y-m-d H:i:s");
        }
        $fecha_nac = new \DateTime(date('Y/m/d',strtotime($opt["Birthday"]))); 
        $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
        $edad = date_diff($fecha_hoy,$fecha_nac); 

        $content="
        <b>NOMBRE:</b> ".ucwords(mb_strtolower($opt["Name"],"utf-8"))."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EDAD:</b> ".$edad->format("%Y")." años"."<br />
        <b>RUT:</b> ".$rut."<br /> 
        <b>DIAGNÓSTICO:</b> ".$opt["Diagnosis"]."<br />   
        ";

        $content.= '<h4>Rp</h4>';
        foreach ($opt["data"] as $recipe) {
            $content .= "- <b>".$recipe->Name."</b><br>".$recipe->Dose."<br><br>";
        }
        $content .= '<br /><br />';

        $path = dirname(__FILE__)."/../../../resources/views/generic.html";
        if (!file_exists($path)) {
            throw new \Exception("not found template ".$path);
        }
        $fp = file_get_contents($path);
        $fp = str_replace("{{html}}",$content,$fp);
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);

        //210x279 
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($fp); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();

    } 
    public function getCertificateSingle($id) {
        setlocale(LC_TIME, 'es_CL.UTF-8','esp');

        $pdfname = "CERT_".$id."_".date("Ymd_hi");

        $cert = Certificate::select(
            'Certificates.*',
            'CertificateTypes.Name as CertificateTypeName',
            'CertificateTypes.Template as Template',
            'Dates.Date as Date',
            'Dates.Time as Time',
            'Dates.AntAllergy',
            'Dates.AntDrugs',
            'Dates.AntSurgical',
            'Dates.AntMedical',
            'Dates.Obs',
            'Peoples.Name as PeopleName',
            'Peoples.Genre as PeopleGenre',
            'Peoples.Lastname as PeopleLastname',
            'Peoples.CardCode as PeopleCardCode',
            'Peoples.Profession as Profession',
            'Peoples.City as City',
            'Peoples.Birthday as Birthday',
            'Diagnosis.Name as DiagnosisName',
            'Anthropometrys.Weight',
            'Anthropometrys.Height',
            'Anthropometrys.Temperature',
            'Surgerys.Name as SurgeryName'
        )
        ->leftJoin('CertificateTypes', 'CertificateTypes.CertificateTypeID', '=', 'Certificates.CertificateTypeID')
        ->leftJoin('Dates', 'Dates.DateID', '=', 'Certificates.DateID')
        ->leftJoin('Peoples', 'Peoples.PeopleID', '=', 'Certificates.PeopleID')
        ->leftJoin('Diagnosis', 'Diagnosis.DiagnosisID', '=', 'Dates.DiagnosisID')
        ->leftJoin('Surgerys', 'Surgerys.SurgeryID', '=', 'Dates.SurgeryID')
        ->leftJoin('Anthropometrys', 'Anthropometrys.DateID', '=', 'Dates.DateID')
        ->where("Certificates.CertificateID",$id)
        ->first();

        $path = dirname(__FILE__)."/../../../resources/views/".$cert["Template"];
        if (!file_exists($path)) {
            throw new \Exception("not found template ".$path);
        }
        $fp = file_get_contents($path);
        $dc = "";
        if ($cert->DateAsSurgery) {
            try {
                $dc = implode("/",array_reverse(explode("-", substr($cert->DateAsSurgery,0,10))));
            } catch (Exception $e2) { }
        }
        
        $imc = 0;
        try {
            $weight = floatval($cert->Weight);
            $height = floatval($cert->Height);
            if ($weight == 0 || $height == 0) {
                throw new \Exception("Division Zero");
            }
            $m2 = ($height/100) * ($height/100);
            $imc = round(($weight / $m2) * 100) / 100;
        } catch (Exception $e2) {
            $imc = 0;
        }


        $edv = DB::select("
        SELECT		E.ExamID,  ET.Name ExamTypeName, E.Name, ED.ExamDataType, ED.Name ExamDataName, EDV.Value 
        FROM		Exams as E 
        INNER JOIN	ExamTypes ET ON ET.ExamTypeID = E.ExamTypeID
        INNER JOIN	ExamDatas ED ON ED.ExamID = E.ExamID 
        INNER JOIN	ExamDataValues EDV ON EDV.ExamDataID = ED.ExamDataID 
        INNER JOIN  Orders O ON O.DateID = '".$cert->DateID."' AND O.OrderID = EDV.OrderID 
        WHERE		E.Active = 1 
        ORDER BY	ET.Name ASC, EDV.ExamDataValueID DESC
        ");
        $edv = json_decode(json_encode($edv), true);
        $results = [];
        foreach ($edv as $rr) {
            if (!isset($results[$rr["ExamTypeName"]][$rr["ExamDataName"]])) { // Only newest result
                if ($rr["ExamDataType"]=="boolean") {
                    if ($rr["Value"]=="1") {
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "OK";
                    }
                    else {
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "NO-OK";
                    }
                }
                else if ($rr["ExamDataType"]=="text") { 
                    $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = $rr["Value"];
                } 
                else if ($rr["ExamDataType"]=="number") { 
                    $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = round($rr["Value"],2);
                }
            }
        }

        $txt_examenes = "";
        foreach ($results as $type=>$d) {
            $txt_examenes .= '<tr><td colspan="2">'.mb_strtoupper($type,'utf-8').'</td></tr>';
            foreach ($d as $field=>$val) {
                $txt_examenes .= '<tr><td class="width-200">'.$field.'</td><td class="width-200">'.$val.'</td></tr>';
            }
        } 

        $txt_medicos = ""; 
        $inv = Interview::select("Interviews.*","Specialists.Name as SpecialistName")
                ->leftJoin("Specialists","Specialists.SpecialistID","=","Interviews.SpecialistID")
                ->where("DateID",$cert->DateID)
                ->get();

        foreach ($inv as $idoc) {
            $txt_medicos .= '<tr><td class="width-200">'.$idoc->SpecialistName.'</td><td class="width-200">'.$idoc->Description.'</td></tr>';
        }

        $ccc = $cert->PeopleCardCode;
        $ccc = str_replace(["-","."],["",""], $ccc);
        $rut = number_format( substr ( $ccc, 0 , -1 ) , 0, "", ".") . '-' . substr ( $ccc, strlen($ccc) -1 , 1 );

        $fecha_nac = new \DateTime(date('Y/m/d',strtotime($cert->Birthday))); 
        $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
        $edad = date_diff($fecha_hoy,$fecha_nac); 

        $ella_el = "el";
        if ($cert->Genre=="F") {
            $ella_el = "la";
        }

        $fp = str_replace("{{nombre}}",ucwords(mb_strtolower($cert->PeopleName." ".$cert->PeopleLastname),"utf-8"),$fp);
        $fp = str_replace("{{edad}}",$edad->format("%Y")." años",$fp);
        $fp = str_replace("{{diagnostico}}",$cert->DiagnosisName,$fp); 
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);
        $fp = str_replace("{{ella_el}}",$ella_el,$fp);
        $fp = str_replace("{{descripcion}}",$cert->Description,$fp);
        $fp = str_replace("{{profession}}",$cert->Profession,$fp);
        $fp = str_replace("{{fecha_cirugia}}",$dc,$fp);
        $fp = str_replace("{{rut}}",$rut,$fp); 
        $fp = str_replace("{{allergy}}",$cert->AntAllergy,$fp); 
        $fp = str_replace("{{drugs}}",$cert->AntDrugs,$fp); 
        $fp = str_replace("{{medical}}",$cert->AntMedical,$fp); 
        $fp = str_replace("{{surgical}}",$cert->AntSurgical,$fp); 
        $fp = str_replace("{{surgery}}",$cert->SurgeryName,$fp); 
        $fp = str_replace("{{imc}}",$imc,$fp); 
        $fp = str_replace("{{weight}}",$cert->Weight. "kgs",$fp); 
        $fp = str_replace("{{height}}",$cert->Height." cms",$fp); 
        $fp = str_replace("{{city}}",$cert->City,$fp); 
        $fp = str_replace("{{obs}}",$cert->Obs,$fp); 
        $fp = str_replace("{{txt_examenes}}",$txt_examenes,$fp); 
        $fp = str_replace("{{txt_medicos}}",$txt_medicos,$fp);  
        $fp = str_replace("{{fecha_es}}",strftime('%A %e de %B de %Y', strtotime($cert->CreatedAt)),$fp); 


        //210x279 
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($fp); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();

    } 
    public function getInterview($id) {
        setlocale(LC_TIME, 'es_CL.UTF-8','esp');

        $pdfname = "INT_".$id."_".date("Ymd_hi");

        $interview =  Interview::select(
            "Interviews.*", 
            "Users.Name as CreatedByName",
            "Specialists.Name as SpecialistName",
            "Peoples.Name as Name",
            "Peoples.CardCode as CardCode",
            "Diagnosis.Name as Diagnosis",
            "Peoples.Birthday as Birthday"
        )
        ->join("Users","Users.UserID","=","Interviews.CreatedUserID")
        ->join("Specialists","Specialists.SpecialistID","=","Interviews.SpecialistID")
        ->leftJoin("Dates","Dates.DateID","=","Interviews.DateID")
        ->leftJoin("Diagnosis","Diagnosis.DiagnosisID","=","Dates.DiagnosisID")
        ->leftJoin("Peoples","Peoples.PeopleID","=","Interviews.PeopleID")
        ->where("Interviews.InterviewID", $id)
        ->orderBy("CreatedAt","DESC")
        ->first(); 

        $ccc = $interview->CardCode;
        $ccc = str_replace(["-","."],["",""], $ccc);
        $rut = number_format( substr ( $ccc, 0 , -1 ) , 0, "", ".") . '-' . substr ( $ccc, strlen($ccc) -1 , 1 );
        if ($interview->Birthday=="") {
            $interview->Birthday = date("Y-m-d H:i:s");
        }
        $fecha_nac = new \DateTime(date('Y/m/d',strtotime($interview->Birthday))); 
        $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
        $edad = date_diff($fecha_hoy,$fecha_nac); 

        $content="
        <b>NOMBRE:</b> ".ucwords(mb_strtolower($interview->Name,"utf-8"))."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EDAD:</b> ".$edad->format("%Y")." años"."<br />
        <b>RUT:</b> ".$rut."<br /> 
        <b>DIAGNÓSTICO:</b> ".$interview->Diagnosis."<br />   
        ";

        $content .= '<h4>Interconsulta</h4><b>Especialidad: </b> '.$interview->SpecialistName.' '.$interview->Description.'<br />'.$interview->WantText.'<br /></page>';   
        $path = dirname(__FILE__)."/../../../resources/views/generic.html";
        if (!file_exists($path)) {
            throw new \Exception("not found template ".$path);
        }
        $fp = file_get_contents($path);
        $fp = str_replace("{{html}}",$content,$fp);
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);
        
        //210x279 
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($content); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();

    } 
}
