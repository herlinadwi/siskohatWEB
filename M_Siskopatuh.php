<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_Siskopatuh extends CI_Model
{
	var $table = "SISKOPATUH_REKENING_PPIU";
	var $select_column = array("KD_PPIU", "NAMA_PPIU", "REKENING_PENAMPUNGAN", "REKENING_OPERASIONAL");
	var $order_column = array(null, null, "REKENING_PENAMPUNGAN", "REKENING_OPERASIONAL");

	public function make_query()
	{
		$this->db->select($this->select_column);
		$this->db->from($this->table);
		if(isset($_POST["search"]["value"]))
		{
			$this->db->like("REKENING_PENAMPUNGAN", $_POST["search"]["value"]);
			$this->db->or_like("REKENING_OPERASIONAL", $_POST["search"]["value"]);
		}
		if(isset($_POST["order"]))
		{
			$this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		}
		else
		{
			$this->db->order_by('KD_PPIU', 'DESC');
		}
	}

	public function make_datatables()
	{
		$this->make_query();

		if($_POST['length'] != -1)
		{
			$this->db->limit($_POST['length'], $_POST['start']);
		}

		$query = $this->db->get();
		return $query->result(); 
	}

	public function get_filtered_data()
	{
		 $this->make_query();  
		 $query = $this->db->get();  
		 return $query->num_rows();  
	}

	public function get_all_data()  
	{  
		$this->db->select('KD_PPIU, NAMA_PPIU, REKENING_PENAMPUNGAN, REKENING_OPERASIONAL');  
		$query = $this->db->get($this->table);

		if ($query->num_rows() > 0) 
		{
			# ada data
			return $query->result();
		}

		return FALSE;
	}

	/*public function get_all_data()
	{
		$query = $this->db->get('SISKOPATUH_REKENING_PPIU');
		return $query->result();
	}*/

	public function save($KD_PPIU, $NAMA_PPIU, $REKENING_PENAMPUNGAN, $REKENING_OPERASIONAL)
	{
		$hasil = $this->db->query("INSERT INTO SISKOPATUH_REKENING_PPIU(KD_PPIU, NAMA_PPIU, REKENING_PENAMPUNGAN, REKENING_OPERASIONAL) VALUES ('$KD_PPIU', '$NAMA_PPIU', '$REKENING_PENAMPUNGAN', '$REKENING_OPERASIONAL')");
		return $hasil;
	}

	/*public function delete_by_kode_ppiu($KD_PPIU)
	{

		$this->db->delete('SISKOPATUH_REKENING_PPIU', array('KD_PPIU' => $KD_PPIU));
		return $this->db->affected_rows() > 1? true:false;
		/*$this->db->where("KD_PPIU", $KD_PPIU);
		$this->db->delete("SISKOPATUH_REKENING_PPIU");
	}*/

	public function updateEmp()
	{
		$KD_PPIU = $this->input->post('KD_PPIU');
		$NAMA_PPIU = $this->input->post('NAMA_PPIU');
		$REKENING_PENAMPUNGAN = $this->input->post('REKENING_PENAMPUNGAN');
		$REKENING_OPERASIONAL = $this->input->post('REKENING_OPERASIONAL');

		$this->db->set('KD_PPIU', $KD_PPIU);
		$this->db->set('NAMA_PPIU', $NAMA_PPIU);
		$this->db->set('REKENING_PENAMPUNGAN', $REKENING_PENAMPUNGAN);
		$this->db->set('REKENING_OPERASIONAL', $REKENING_OPERASIONAL);
		$this->db->where('KD_PPIU', $KD_PPIU);
		$result = $this->db->update($this->table);
		return $result;
	}

	/*public function delete_data($table, $KD_PPIU)
	{
		$this->db->delete($table, array('KD_PPIU' => $KD_PPIU));
	}*/

	public function delete($KD_PPIU)
	{
		return $this->db->delete($this->table, array("KD_PPIU" => $KD_PPIU));
	}

}

?>
