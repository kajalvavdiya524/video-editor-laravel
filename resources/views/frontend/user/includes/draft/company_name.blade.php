@php
 if ( $row->company_id == 0 )
    echo "All companies";
else    
    echo $row->company->name;
@endphp