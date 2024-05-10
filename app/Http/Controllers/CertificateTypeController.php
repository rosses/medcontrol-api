<?php

namespace App\Http\Controllers;

use App\Models\CertificateType;
use App\Models\Date;
use App\Models\Interview;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
class CertificateTypeController extends Controller
{
    public function index(Request $request) { 
        $rows = CertificateType::select('CertificateTypes.*')
                ->where('CertificateTypes.Active',1)
                ->orderBy('CertificateTypes.Name','ASC')
                ->get();
        $rows = json_decode(json_encode($rows),true);

        if (isset($request->DateID)) {
            $cert = Date::select(
                'Dates.Date as Date',
                'Dates.Time as Time',
                'Dates.AntAllergy',
                'Dates.AntHabits',
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
            ->leftJoin('Peoples', 'Peoples.PeopleID', '=', 'Dates.PeopleID')
            ->leftJoin('Diagnosis', 'Diagnosis.DiagnosisID', '=', 'Dates.DiagnosisID')
            ->leftJoin('Surgerys', 'Surgerys.SurgeryID', '=', 'Dates.SurgeryID')
            ->leftJoin('Anthropometrys', 'Anthropometrys.DateID', '=', 'Dates.DateID')
            ->where("Dates.DateID",$request->DateID)
            ->first();

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
            INNER JOIN  Orders O ON O.DateID = '".$request->DateID."' AND O.OrderID = EDV.OrderID 
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
        }
        foreach ($rows as $idx=>$row) {
            if ($rows[$idx]["templateHtml"]=="") {
                $path = dirname(__FILE__)."/../../../resources/views/".$row["Template"];
                if (!file_exists($path)) {
                    $rows[$idx]["templateHtml"] = "";    
                } else {
                    $fp = file_get_contents($path);

                    // var replacement
                    if (isset($request->DateID)) {
   
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
                        $fp = str_replace("{{habits}}",$cert->AntHabits,$fp); 
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
                        $fp = str_replace("{{fecha_es}}",strftime('%A %e de %B de %Y', time()),$fp); 
                    }

                    $rows[$idx]["templateHtml"] = $fp;
                }
            }
        }
        

        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(CertificateType::find($id));
    }

    public function create(Request $request) {
        $row = CertificateType::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = CertificateType::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        CertificateType::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
