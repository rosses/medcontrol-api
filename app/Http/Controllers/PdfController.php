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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Spipu\Html2Pdf\Html2Pdf;

class PdfController extends Controller
{
    public function getDataOrders($id) {
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
        $html2pdf->writeHTML($content); 
        //$html2pdf->output(public_path().'/uploads/'.$x["Comments"].'.pdf','F');
        //header("Content-Type: text/html");
        //echo "<script type='text/javascript'> location.href= '".env('APP_URL').'/uploads/'.$x["Comments"].'.pdf'."'; </script>"; 
        $html2pdf->output();
        die();
        //header("Content-Type: application/pdf");
        //readfile(public_path().'/uploads/'.$x["Comments"].'.pdf');

    } 
}
