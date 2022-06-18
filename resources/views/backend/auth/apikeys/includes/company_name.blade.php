@php

if ( $apikey->company_id == 0 )
    echo "All companies";
else    
    echo $apikey->company->name;

@endphp