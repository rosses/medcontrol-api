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

class PdfController extends Controller
{
    public function getDataOrders($id) {
        $pdfname = "";
        $packs = Order::select(
            'Orders.DateID',
            'Dates.Date as Date',
            'Dates.Time as Time',
            'Peoples.Name as PeopleName',
            'Peoples.Lastname as PeopleLastname',
            'Diagnosis.Name as DiagnosisName',
            'Peoples.CardCode as PeopleCardCode'
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
            'Peoples.CardCode'
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
                "CardCode" => $pack->PeopleCardCode
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


        $page_header = '<page_header></page_header>'; 
        $page_footer = '<page_footer>
            <div style="text-align:right;">
            <img src="firmasalinas.png" width="180" />
            </div>
            <div style="text-align:left; font-size:11px;">
                Fecha, '.date("d/m/Y").'<br />
                Centro de Cirugía Digestiva y Obesidad Clínica Puerto Varas<br />
                www.drsalinas.cl
            </div>
        </page_footer>';
        //$page_footer = '<page_footer>[[page_cu]]/[[page_nb]]</page_footer>'; 
        $content = '
        <style type="text/css">
            <!-- 
            .single-table { padding: 3px; }
            .width-740 { width: 740px; }
            .width-560 { width: 560px; }
            .width-550 { width: 550px; }
            .width-380 { width: 380px; }
            .width-330 { width: 330px; }
            .width-440 { width: 440px; }
            .width-280 { width: 280px; }
            .width-250 { width: 250px; }
            .width-200 { width: 200px; }
            .width-150 { width: 150px; }
            .width-170 { width: 170px; }
            .width-160 { width: 160px; }
            .width-110 { width: 110px; }
            .width-120 { width: 120px; }
            .width-100 { width: 100px; }
            .width-90 { width: 90px; }
            .width-80 { width: 80px; }
            .width-75 { width: 75px; }
            .width-75 { width: 70px; }
            .width-65 { width: 65px; }
            .width-60 { width: 60px; }
            .width-55 { width: 55px; }
            .width-50 { width: 50px; }
            .width-45 { width: 45px; }
            .width-40 { width: 40px; }
            .width-35 { width: 35px; }
            .width-30 { width: 30px; }
            .text-center { text-center:left; }
            .text-left { text-align:left; }
            .b { font-weight: bold; }
            .cola {
                border-collapse: collapse;
            }
            .cola td {
                border:1px solid black;
                font-size: 10px;
                padding:3px;
                height: 5px;
                border-collapse: collapse;
            }
            --> 
        </style>';
        
        foreach ($output as $dates) {
            foreach ($dates["data"] as $datas) {
                $content.= '
                <page format="140x200" backtop="8mm" backbottom="20mm" backleft="0mm" backright="0mm">
                '.$page_header.'
                '.$page_footer.'
                <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="width-330">
                        <h4 style="margin:0;padding:0;" class="text-left">DR. JOSÉ SALINAS ACEVEDO</h4>
                        <h4 style="margin:0;padding:0;font-weight:normal;" class="text-left">
                        Cirugía Digestiva<br />P. Universidad Católica de Chile</h4>
                        <br />
                        Bypass gástrico, gastrectomía en manga<br />
                        Reflujo gastroesofágico, hernias vía laparoscópica<br />
                        Oncología tracto gastrointesnal
                    </td>
    
                    <td class="width-160" style="text-align:right;">
                        <img src="logosalinas.png" width="120" />
                    </td>
                </tr>
                </table>
                <hr />
                Nombre: '.$dates["Name"].'<br />
                Rut: '.$dates["CardCode"].'<br />
                Diagnóstico: '.$dates["Diagnosis"].'<br />
                <h4>Rp</h4>
                <b>'.$datas["ExamTypeName"].'</b><br><br>
                ';

                foreach ($datas["Exams"] as $exm) {
                    $content .= "- ".$exm."<br>";
                }
                $content .= '<br /><br /></page>';   
            }
        }

        //210x279 
        $md5 = md5(time());
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($content); 
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

        $page_header = '<page_header></page_header>'; 
        $page_footer = '<page_footer>
            <div style="text-align:right;">
            <img src="firmasalinas.png" width="180" />
            </div>
            <div style="text-align:left; font-size:11px;">
                Fecha, '.date("d/m/Y").'<br />
                Centro de Cirugía Digestiva y Obesidad Clínica Puerto Varas<br />
                www.drsalinas.cl
            </div>
        </page_footer>';
        $content = '
        <style type="text/css">
            <!-- 
            .single-table { padding: 3px; }
            .width-740 { width: 740px; }
            .width-560 { width: 560px; }
            .width-550 { width: 550px; }
            .width-380 { width: 380px; }
            .width-330 { width: 330px; }
            .width-440 { width: 440px; }
            .width-280 { width: 280px; }
            .width-250 { width: 250px; }
            .width-200 { width: 200px; }
            .width-150 { width: 150px; }
            .width-170 { width: 170px; }
            .width-160 { width: 160px; }
            .width-110 { width: 110px; }
            .width-120 { width: 120px; }
            .width-100 { width: 100px; }
            .width-90 { width: 90px; }
            .width-80 { width: 80px; }
            .width-75 { width: 75px; }
            .width-75 { width: 70px; }
            .width-65 { width: 65px; }
            .width-60 { width: 60px; }
            .width-55 { width: 55px; }
            .width-50 { width: 50px; }
            .width-45 { width: 45px; }
            .width-40 { width: 40px; }
            .width-35 { width: 35px; }
            .width-30 { width: 30px; }
            .text-center { text-center:left; }
            .text-left { text-align:left; }
            .b { font-weight: bold; }
            .cola {
                border-collapse: collapse;
            }
            .cola td {
                border:1px solid black;
                font-size: 10px;
                padding:3px;
                height: 5px;
                border-collapse: collapse;
            }
            --> 
        </style>';

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
        $content.= '
        <page format="216x280" backtop="4mm" backbottom="4mm" backleft="0mm" backright="0mm">
        '.$page_header.'
        '.$page_footer.'
        <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="width-160" style="text-align:left;">
                <img src="logosalinas.png" width="120" />
            </td>
            <td class="width-250">
                <h5 style="margin:0;padding:0;" class="text-left">DR. JOSÉ SALINAS ACEVEDO</h5>
                <h5 style="margin:0;padding:0;font-weight:normal;" class="text-left">
                Cirugía Digestiva<br />P. Universidad Católica de Chile</h5>
            </td>
            <td class="width-330">
                Ficha del paciente
                <h4>'.$people->Name.' '.$people->Lastname.' </h4>
            </td>

        </tr>
        </table>
        <hr />
        <table width="100%">
          <tr>
            <td class="width-330">
                <h5>Información</h5>
                <table width="100%" valign="top">
                <tr><td class="width-120"><b>Nombre:</b></td><td>'.$people->Name.' '.$people->Lastname.'</td></tr>
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
            <tr><td class="width-120"><b>Peso (kg):</b></td><td>'.number_format($ant->Weight,1,",",".").'</td></tr>
            <tr><td class="width-120"><b>Altura (cm):</b></td><td>'.number_format($ant->Height,0,",",".").'</td></tr>
            <tr><td class="width-120"><b>IMC:</b></td><td>'.number_format($imc,1,",",".").'</td></tr>
            <tr><td class="width-120"><b>Médicos:</b></td><td>'.($ant->AntMedical).'</td></tr>
            <tr><td class="width-120"><b>Alergias:</b></td><td>'.($ant->AntAllergy).'</td></tr>
            <tr><td class="width-120"><b>Quirúrgicos:</b></td><td>'.($ant->AntSurgical).'</td></tr>
            <tr><td class="width-120"><b>Fármacos:</b></td><td>'.($ant->AntDrugs).'</td></tr>
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
        </table>
        ';

        $content .= '<br /><br /></page>';   


        //210x279
        $md5 = md5(time());
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($content); 
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
            'Peoples.CardCode as PeopleCardCode'
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
            'Peoples.CardCode'
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
        if ($packs[0]->PeopleName != "" && $packs[0]->PeopleLastname != "") {
            $opt["Name"] = $packs[0]->PeopleName." ".$packs[0]->PeopleLastname;
            $pdfname = $packs[0]->PeopleName." ".$packs[0]->PeopleLastname;
        }
        if ($packs[0]->DiagnosisName != "") {
            $opt["Diagnosis"] = $packs[0]->DiagnosisName;
        }


        $page_header = '<page_header></page_header>'; 
        $page_footer = '<page_footer>
            <div style="text-align:right;">
            <img src="firmasalinas.png" width="180" />
            </div>
            <div style="text-align:left; font-size:11px;">
                Fecha, '.date("d/m/Y").'<br />
                Centro de Cirugía Digestiva y Obesidad Clínica Puerto Varas<br />
                www.drsalinas.cl
            </div>
        </page_footer>';
        //$page_footer = '<page_footer>[[page_cu]]/[[page_nb]]</page_footer>'; 
        $content = '
        <style type="text/css">
            <!-- 
            .single-table { padding: 3px; }
            .width-740 { width: 740px; }
            .width-560 { width: 560px; }
            .width-550 { width: 550px; }
            .width-380 { width: 380px; }
            .width-330 { width: 330px; }
            .width-440 { width: 440px; }
            .width-280 { width: 280px; }
            .width-250 { width: 250px; }
            .width-200 { width: 200px; }
            .width-150 { width: 150px; }
            .width-170 { width: 170px; }
            .width-160 { width: 160px; }
            .width-110 { width: 110px; }
            .width-120 { width: 120px; }
            .width-100 { width: 100px; }
            .width-90 { width: 90px; }
            .width-80 { width: 80px; }
            .width-75 { width: 75px; }
            .width-75 { width: 70px; }
            .width-65 { width: 65px; }
            .width-60 { width: 60px; }
            .width-55 { width: 55px; }
            .width-50 { width: 50px; }
            .width-45 { width: 45px; }
            .width-40 { width: 40px; }
            .width-35 { width: 35px; }
            .width-30 { width: 30px; }
            .text-center { text-center:left; }
            .text-left { text-align:left; }
            .b { font-weight: bold; }
            .cola {
                border-collapse: collapse;
            }
            .cola td {
                border:1px solid black;
                font-size: 10px;
                padding:3px;
                height: 5px;
                border-collapse: collapse;
            }
            --> 
        </style>';
          
        $content.= '
        <page format="140x200" backtop="8mm" backbottom="8mm" backleft="0mm" backright="0mm">
        '.$page_header.'
        '.$page_footer.'
        <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="width-330">
                <h4 style="margin:0;padding:0;" class="text-left">DR. JOSÉ SALINAS ACEVEDO</h4>
                <h4 style="margin:0;padding:0;font-weight:normal;" class="text-left">
                Cirugía Digestiva<br />P. Universidad Católica de Chile</h4>
                <br />
                Bypass gástrico, gastrectomía en manga<br />
                Reflujo gastroesofágico, hernias vía laparoscópica<br />
                Oncología tracto gastrointesnal
            </td>

            <td class="width-160" style="text-align:right;">
                <img src="logosalinas.png" width="120" />
            </td>
        </tr>
        </table>
        <hr />
        Nombre: '.$opt["Name"].'<br />
        Rut: '.$opt["CardCode"].'<br />
        Diagnóstico: '.$opt["Diagnosis"].'<br />
        <h4>Rp</h4> 
        ';

        foreach ($opt["data"] as $recipe) {
            $content .= "- <b>".$recipe->Name."</b><br>Dosis: ".$recipe->Dose.", ".$recipe->Period." veces por ".$recipe->Periodicity." dias<br><br>";
        }
        $content .= '<br /><br /></page>';   
    
    

        //210x279 
        $md5 = md5(time());
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($content); 
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
            'Peoples.Name as PeopleName',
            'Peoples.Lastname as PeopleLastname',
            'Peoples.CardCode as PeopleCardCode',
            'Peoples.City as City',
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
            $txt_examenes .= "\n".mb_strtoupper($type,'utf-8')."\n";
            foreach ($d as $field=>$val) {
                $txt_examenes .= "".$field.": ".$val."\n";
            }
        } 

        $txt_medicos = "";


        $fp = str_replace("{{fecha_cirugia}}",$dc,$fp);
        $fp = str_replace("{{fecha}}",date("d/m/Y"),$fp);
        $fp = str_replace("{{nombre}}",$cert->PeopleName." ".$cert->PeopleLastname,$fp);
        $fp = str_replace("{{rut}}",$cert->PeopleCardCode,$fp); 
        $fp = str_replace("{{allergy}}",$cert->AntAllergy,$fp); 
        $fp = str_replace("{{drugs}}",$cert->AntDrugs,$fp); 
        $fp = str_replace("{{medical}}",$cert->AntMedical,$fp); 
        $fp = str_replace("{{surgical}}",$cert->AntSurgical,$fp); 
        $fp = str_replace("{{surgery}}",$cert->SurgeryName,$fp); 
        $fp = str_replace("{{diagnostico}}",$cert->DiagnosisName,$fp); 
        $fp = str_replace("{{imc}}",$imc,$fp); 
        $fp = str_replace("{{weight}}",$cert->Weight. "kgs",$fp); 
        $fp = str_replace("{{height}}",$cert->Height." cms",$fp); 
        $fp = str_replace("{{city}}",$cert->City,$fp); 
        $fp = str_replace("{{txt_examenes}}",$txt_examenes,$fp); 
        $fp = str_replace("{{txt_medicos}}",$txt_medicos,$fp); 
        $fp = str_replace("{{fecha_es}}",strftime('%A %e de %B de %Y', strtotime($cert->CreatedAt)),$fp); 


        //210x279 
        $md5 = md5(time());
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
            "Diagnosis.Name as Diagnosis"
        )
        ->join("Users","Users.UserID","=","Interviews.CreatedUserID")
        ->join("Specialists","Specialists.SpecialistID","=","Interviews.SpecialistID")
        ->leftJoin("Dates","Dates.DateID","=","Interviews.DateID")
        ->leftJoin("Diagnosis","Diagnosis.DiagnosisID","=","Dates.DiagnosisID")
        ->leftJoin("Peoples","Peoples.PeopleID","=","Interviews.PeopleID")
        ->where("Interviews.InterviewID", $id)
        ->orderBy("CreatedAt","DESC")
        ->first();


        $page_header = '<page_header></page_header>'; 
        $page_footer = '<page_footer>
            <div style="text-align:right;">
            <img src="firmasalinas.png" width="180" />
            </div>
            <div style="text-align:left; font-size:11px;">
                Fecha, '.date("d/m/Y").'<br />
                Centro de Cirugía Digestiva y Obesidad Clínica Puerto Varas<br />
                www.drsalinas.cl
            </div>
        </page_footer>';
        $content = '
        <style type="text/css">
            <!-- 
            .single-table { padding: 3px; }
            .width-740 { width: 740px; }
            .width-560 { width: 560px; }
            .width-550 { width: 550px; }
            .width-380 { width: 380px; }
            .width-330 { width: 330px; }
            .width-440 { width: 440px; }
            .width-280 { width: 280px; }
            .width-250 { width: 250px; }
            .width-200 { width: 200px; }
            .width-150 { width: 150px; }
            .width-170 { width: 170px; }
            .width-160 { width: 160px; }
            .width-110 { width: 110px; }
            .width-120 { width: 120px; }
            .width-100 { width: 100px; }
            .width-90 { width: 90px; }
            .width-80 { width: 80px; }
            .width-75 { width: 75px; }
            .width-75 { width: 70px; }
            .width-65 { width: 65px; }
            .width-60 { width: 60px; }
            .width-55 { width: 55px; }
            .width-50 { width: 50px; }
            .width-45 { width: 45px; }
            .width-40 { width: 40px; }
            .width-35 { width: 35px; }
            .width-30 { width: 30px; }
            .text-center { text-center:left; }
            .text-left { text-align:left; }
            .b { font-weight: bold; }
            .cola {
                border-collapse: collapse;
            }
            .cola td {
                border:1px solid black;
                font-size: 10px;
                padding:3px;
                height: 5px;
                border-collapse: collapse;
            }
            --> 
        </style>';

        $content.= '
        <page format="140x200" backtop="8mm" backbottom="20mm" backleft="0mm" backright="0mm">
        '.$page_header.'
        '.$page_footer.'
        <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="width-330">
                <h4 style="margin:0;padding:0;" class="text-left">DR. JOSÉ SALINAS ACEVEDO</h4>
                <h4 style="margin:0;padding:0;font-weight:normal;" class="text-left">
                Cirugía Digestiva<br />P. Universidad Católica de Chile</h4>
                <br />
                Bypass gástrico, gastrectomía en manga<br />
                Reflujo gastroesofágico, hernias vía laparoscópica<br />
                Oncología tracto gastrointesnal
            </td>

            <td class="width-160" style="text-align:right;">
                <img src="logosalinas.png" width="120" />
            </td>
        </tr>
        </table>
        <hr />
        Nombre: '.$interview->Name.'<br />
        Rut: '.$interview->CardCode.'<br />
        Diagnóstico: '.$interview->Diagnosis.'<br />
        <h4>Interconsulta</h4>
        <b>Especialidad</b>'.$interview->SpecialistName.'<br><br>
        ';

        $content .= $interview->Description.'<br />'.$interview->WantText.'<br /></page>';   
    

        //210x279 
        $md5 = md5(time());
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', 5);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->pdf->setTitle("pdf ".$pdfname.".pdf");
        $html2pdf->writeHTML($content); 
        $html2pdf->output("pdf ".$pdfname.".pdf");
        die();

    } 
}
