@php

echo " API key #". $api_key->id . " - ";    

if ( $api_key->company_id == '' )
    echo "All companies";
else    
    echo $api_key->company->name;
   
@endphp