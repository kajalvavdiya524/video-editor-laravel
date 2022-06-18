<?php

namespace App\Http\Controllers;

use App\Domains\Auth\Models\ApiKeys;
use App\Domains\Auth\Models\Job;
use App\Domains\Auth\Models\JobDetails;
use App\Domains\Auth\Models\Template;
//use Composer\DependencyResolver\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use stdClass;

/**
 * Class LocaleController.
 */
class ApiController extends Controller
{

     /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
  
     public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
 
    public function templates($id = null)
    {
        if (!$id)
            $result = Template::all();
        else 
            $result = Template::find($id);
        
            if ($result)
            return $this->sendResponse($result,'Template List');
        else
            return $this->sendError("Template not found",null,404); 
    }

    public function templates_customer($customer_id)
    {
     
        $result = Template::where('customer_id', $customer_id)->get();
        
        if (count($result))
            return $this->sendResponse($result,'Template List');
        else
        return $this->sendError("Templates not found",null,404); 
    }


    public function template_fields(Request $request,$id)
    {
     
        $validator = Validator::make($request->route()->parameters(), [
            "id" => "required|integer|exists:templates,id",
        ]);

        if ($validator->fails()) {
            return $this->sendError("Invalid request",$validator->errors(),400); 
        }

        $data = $validator->validated();
        $template = Template::find($id);
      
        $return =  $template->getApiFields();

        return $this->sendResponse($return,'Available fields for template: '.$id);

    }



    public function create_job(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "input" => "required|JSON",
            "template_id" => "required|integer|exists:templates,id",
            "job_type" => "required|integer|exists:job_types,id",
        ]);
    
        if ($validator->fails()) {
            return $this->sendError("Invalid request",$validator->errors(),400); 
        }

        $data = $validator->validated();

        // retrieve api key used
        $token = EnsureTokenIsValid::get_token($request);
        $api_key = ApiKeys::findByToken($token);

        
        /* ******************************************* */
        // check input data, for now just check if the 
        // fields exists 
        /* ******************************************* */

        $template = Template::find($data['template_id']);
        $template_fields = $template->getFieldsArray(false, true);

        $unrecognized_fields = array();
        $input = json_decode($data['input'],1);

        // check if input is an array
        if (!is_array($input))
            return $this->sendError("Wrong input format, must be an array","",400); 
        
        foreach ($input as $key => $template_values){
            
            // check if each element is an array
            if (!is_array($template_values))
            return $this->sendError("Wrong format for element #$key, must be an array of data","",400); 
            

            foreach ($template_values as $key => $field){
           
            // check if json input format is valid            
            /*if (!property_exists($field, 'name') || !property_exists($field, 'value'))
                return $this->sendError("Invalid format must have 'name' and 'value'","",400); */

            // check if field exists in template   
            if (!isset($template_fields[$key])){
               $unrecognized_fields[]=$key;
            }


            }
        }

        if (count($unrecognized_fields)){
            return $this->sendError("Unrecognized input fields",$unrecognized_fields,400); 
        }
        /* ******************************************* */
        // END check input data
        /* ******************************************* */
 

        /* Save Job */
        $job = new Job();
        $job->api_keys_id = $api_key->id;
        $job->template_id = $data['template_id'] ;
        $job->job_types_id = $data['job_type'] ;
        // status pending/new
        $job->job_statuses_id = 1 ;
        $job->save();
       
        if (!$job)
            return $this->sendError("There was an error creating the job, please check your input",null,400); 
            
        


        foreach ($input as $key => $template_values){
            $job_detail = new JobDetails();
            $job_detail->job_id = $job->id;
            $job_detail->input = json_encode($template_values);
            $job_detail->job_statuses_id = 1 ;
            $job_detail->save();

            if (!$job_detail)
                return $this->sendError("There was an error creating the job, please check your input",null,400); 
        

        }


        return $this->sendResponse($job,'Job created sucessfully');
     
       
     
    }





}

