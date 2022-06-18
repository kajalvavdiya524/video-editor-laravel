<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\NewMapping;
use App\Domains\Auth\Models\File;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NewMappingImport implements ToCollection, WithMultipleSheets
{

    public function sheets(): array 
    {
        return [
            0 => $this,
        ];
    }

    
    /**
     * Returns the size of a file without downloading it, or -1 if the file
     * size could not be determined.
     *
     * @param $url - The location of the remote file to download. Cannot
     * be null or empty.
     *
     * @return The size of the file referenced by $url, or -1 if the size
     * could not be determined.
     */
    public function curl_get_file_size( $url ) {
        // Assume failure.
        $result = -1;
    
        $curl = curl_init( $url );
    
        // Issue a HEAD request and follow any redirects.
        curl_setopt( $curl, CURLOPT_NOBODY, true );
        curl_setopt( $curl, CURLOPT_HEADER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']??null );
    
        $data = curl_exec( $curl );
        curl_close( $curl );
    
        if( $data ) {
            $content_length = "unknown";
            $status = "unknown";
        
            if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
                $status = (int)$matches[1];
            }
        
            if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
                $content_length = (int)$matches[1];
            }
        
            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if( $status == 200 || ($status > 300 && $status <= 308) ) {
                $result = $content_length;
            }
        }
    
        return $result;
    }

    public function collection(Collection $rows)
    {
        $company_id = auth()->user()->company_id;
        foreach ($rows as $row) 
        {
            $GTIN = $row[0];
            $child_links = $row[1];
            
            if ($GTIN != 'GTIN' && $child_links != 'Child Links' && isset($GTIN)) {
                $exist = NewMapping::where('GTIN', $GTIN)->first();
                if ($exist && $exist->company_id == $company_id) {
                    $exist->GTIN = $GTIN;
                    $exist->child_links = $child_links ? $child_links : '';
                    $exist->ASIN = $row[2] ? $row[2] : '';
                    $exist->brand = $row[3];
                    $exist->product_name = $row[4];
                    $exist->status = " ";
                    if ($exist->image_url != $row[5] && $row[5] != "") {
                        $ori_size = $this->curl_get_file_size($exist->image_url);
                        $new_size = $this->curl_get_file_size($row[5]);
                        if ($ori_size != $new_size) {
                            $exist->status = 'changed_prod';
                        }
                    }
                    if ($exist->nf_url != $row[6] && $row[6] != "") {
                        $ori_size = $this->curl_get_file_size($exist->nf_url);
                        $new_size = $this->curl_get_file_size($row[6]);
                        if ($ori_size != $new_size) {
                            $exist->status .= ' changed_nf';
                        }
                    }
                    if ($exist->ingredient_url != $row[7] && $row[7] != "") {
                        $ori_size = $this->curl_get_file_size($exist->ingredient_url);
                        $new_size = $this->curl_get_file_size($row[7]);
                        if ($ori_size != $new_size) {
                            $exist->status .= ' changed_ingre';
                        }
                    }
                    $exist->image_url = $row[5];
                    $exist->nf_url = $row[6];
                    $exist->ingredient_url = $row[7];

                    $exist->width = $row[8];
                    $exist->height = $row[9];
                    $exist->depth = $row[10];
                    $exist->company_id = $company_id;

                    $exist->save();
                } else {
                    $status = '';
                    $file = File::where('name', $GTIN.'.png')->first();
                    if ($file) {
                        $status = 'file exist';
                    } else {
                        if ($row[5] != "" || $row[6] != "" || $row[7] != "") {
                            $status = 'new';
                        } else {
                            $status = 'child_links';
                        }
                    }
                    NewMapping::create([
                        'GTIN' => $GTIN,
                        'child_links' => $child_links ? $child_links : '',
                        'ASIN' => $row[2] ? $row[2] : '',
                        'brand' => $row[3],
                        'product_name' => $row[4],
                        'image_url' => $row[5],
                        'nf_url' => $row[6],
                        'ingredient_url' => $row[7],
                        'width' => $row[8],
                        'height' => $row[9],
                        'depth' => $row[10],
                        'company_id' => $company_id,
                        'status' => $status
                    ]);
                }
            }
        }
    }
}