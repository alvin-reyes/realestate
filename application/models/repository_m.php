<?php

class Repository_m extends MY_Model {
    
    protected $_table_name = 'repository';
    protected $_order_by = 'id';
    
	public function delete ($id)
	{
        // Delete all files from filesystem
        $files = $this->file_m->get_by(array(
            'repository_id' => $id
        ));
        
        foreach($files as $file)
        {
            if(file_exists(FCPATH.'files/'.$file->filename))
                unlink(FCPATH.'files/'.$file->filename);
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
                unlink(FCPATH.'files/thumbnail/'.$file->filename);
        }
       
        // Delete all files from db
        $this->db->where('repository_id', $id);
        $this->db->delete($this->file_m->get_table_name()); 
        
        // Delete repository row
        parent::delete($id);
	}
    
}



