<?php

namespace DoubleThreeDigital\Runway\Models;

use Illuminate\Database\Eloquent\Model;

class SearchDocument
{
    private $runwayId;
    private $runwayTableName;
    
    public function data($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }
    
    public function get($value)
    {
        return $this->$value ?? '';
    }
    
    public function id($id)
    {
        $this->runwayId = $id;

        return $this;
    }
    
    public function table($table)
    {
        $this->runwayTableName = $table;
        return $this;
    }

    public function reference()
    {
        return 'runway::'.$this->runwayTableName.'::'.$this->runwayId;
    }
}
