<?php

namespace App\Http\Controllers;
use App\Models\Template;
use App\Models\TemplateCertificate;
use App\Models\TemplateInterview;
use App\Models\TemplateOrder;
use App\Models\TemplateRecipe;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class TemplateController extends Controller
{
    public function show($surgeryID) {
        $template = Template::where("SurgeryID", $surgeryID)->first();
        if (!$template) {
            $template = new Template();
            $template->SurgeryID = $surgeryID; 
            $template->CreatedUserID = JWTAuth::user()->UserID;
            $template->CreatedAt = date("Y-m-d H:i:s");
            $template->save();
        }
      
        $template["Orders"] = TemplateOrder::where("TemplateID", $template->TemplateID)->get();
        $template["Recipes"] = TemplateRecipe::where("TemplateID", $template->TemplateID)->get();
        $template["Interviews"] = TemplateInterview::where("TemplateID", $template->TemplateID)->get();
        $template["Certificates"] = TemplateCertificate::where("TemplateID", $template->TemplateID)->get();
        return response()->json($template);
    }

    public function update($surgeryID, Request $request) {
        $template = Template::where("SurgeryID", $surgeryID)->first();
        TemplateOrder::where("TemplateID", $template->TemplateID)->delete();
        TemplateRecipe::where("TemplateID", $template->TemplateID)->delete();
        TemplateInterview::where("TemplateID", $template->TemplateID)->delete();
        TemplateCertificate::where("TemplateID", $template->TemplateID)->delete();

        if (is_array($request->Certificates)) {
            foreach ($request->Certificates as $o) {
                $item = new TemplateCertificate();
                $item->TemplateID = $template->TemplateID;
                $item->CertificateTypeID = $o["CertificateTypeID"];
                $item->Description = $o["Description"];
                $item->save();
            }
        }

        if (is_array($request->Interviews)) {
            foreach ($request->Interviews as $o) {
                $item = new TemplateInterview();
                $item->TemplateID = $template->TemplateID;
                $item->DiagnosisID = $o["DiagnosisID"];
                $item->SpecialistID = $o["SpecialistID"];
                $item->Description = $o["Description"];
                $item->save();
            }
        }

        if (is_array($request->Orders)) {
            foreach ($request->Orders as $o) {
                $item = new TemplateOrder();
                $item->TemplateID = $template->TemplateID;
                $item->ExamID = $o["ExamID"]; 
                $item->ExamTypeID = $o["ExamTypeID"];
                $item->Description = $o["Description"];
                $item->save();
            }
        }

        if (is_array($request->Recipes)) {
            foreach ($request->Recipes as $o) {
                $item = new TemplateRecipe();
                $item->TemplateID = $template->TemplateID;
                $item->Dose = $o["Dose"];
                $item->MedicineID = $o["MedicineID"];
                $item->Period = $o["Period"];
                $item->Periodicity = $o["Periodicity"];
                $item->save();
            }
        }

        return response()->json($template, 200);
    }
}
