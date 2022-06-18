<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\NewMapping;
use App\Domains\Auth\Models\File;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UrlsFileImport implements ToCollection
{
    private $rows_count = 0;
    private $id_col = "";
    private $new_prod = 0,
            $new_nf = 0, 
            $new_ingr = 0;
    private $changed_prod = 0, 
            $changed_nf = 0,
            $changed_ingr = 0;

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
        $row_array = $rows->unique();
        $header = $rows->first();
        $this->id_col = $header[0];
        foreach ($row_array as $row) {
            $is_empty = 0;
            for ($i = 0; $i < count($row); $i ++) {
                if (isset($row[$i]) && !empty($row[$i])) {
                    $is_empty = 1;
                }
            }
            $this->rows_count += $is_empty;
        }

        $unique = $rows->unique()->values()->all();
        foreach ($unique as $row) 
        {
            $GTIN = $row[0];
            $child_links = $row[1];
            if ($GTIN != 'GTIN' && $child_links != 'Child Links' && isset($GTIN)) {
                $exist = NewMapping::where('GTIN', $GTIN)->first();
                if ((isset($row[5]) && isset($row[6]) && isset($row[7])) && ($row[5] != "" || $row[6] != "" || $row[7] != "")) {
                    if (($exist && $exist->company_id == $company_id)) {
                        if ($exist->image_url != $row[5] && $row[5] != "") {
                            $ori_size = $this->curl_get_file_size($exist->image_url);
                            $new_size = $this->curl_get_file_size($row[5]);
                            if ($ori_size != $new_size) {
                                $this->changed_prod ++;
                            }
                        }
                        if ($exist->nf_url != $row[6] && $row[6] != "") {
                            $ori_size = $this->curl_get_file_size($exist->nf_url);
                            $new_size = $this->curl_get_file_size($row[6]);
                            if ($ori_size != $new_size) {
                                $this->changed_nf ++;
                            }
                        }
                        if ($exist->ingredient_url != $row[7] && $row[7] != "") {
                            $ori_size = $this->curl_get_file_size($exist->ingredient_url);
                            $new_size = $this->curl_get_file_size($row[7]);
                            if ($ori_size != $new_size) {
                                $this->changed_ingr ++;
                            }
                        }
                    } else {
                        $file = File::where('name', $GTIN.'.png')->first();
                        if (!$file) {
                            if ($row[5] != "") {
                                $this->new_prod ++;
                            }
                        }
                    }
                }
            }
        }
    }
    

    public function getRowCount(): int
    {
        return $this->rows_count - 1;
    }

    public function getIdColumn()
    {
        return $this->id_col;
    }

    public function getNewCount($type): int
    {
        if ($type == "Products") return $this->new_prod;
        if ($type == "Nutrition_Facts") return $this->new_nf;
        if ($type == "Ingredients") return $this->new_ingr;
        return 0;
    }
    
    public function getChangedCount($type): int
    {
        if ($type == "Products") return $this->changed_prod;
        if ($type == "Nutrition_Facts") return $this->changed_nf;
        if ($type == "Ingredients") return $this->changed_ingr;
        return 0;
    }
}