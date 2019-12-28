<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class C_Main extends Frontend_Controller {
	
	var $template = 'template';
	var $templates = "template";

	public function __construct(){
		parent::__construct();
		$this->load->model('m_master');
		//Tambahan Source untuk CR 8239-Otomasi Pencairan Rek BPIU ke Opr PPIU
		$this->load->model('m_siskopatuh');

		$this->load->library('table');
		//Tambahan Source untuk CR 8239-Otomasi Pencairan Rek BPIU ke Opr PPIU
		$this->load->library('form_validation');

		$this->load->library('insertclob');
		$this->load->library('nativesession');
	}
	
	public function index(){
		$this->data['meta_title']	= "Hajj Application System";
		$USER_EMAIL = $this->nativesession->get('USER_EMAIL');
		$ID_LEVEL = $this->nativesession->get('ID_LEVEL');
		if($ID_LEVEL == 1){
			$level = "Admin";
		}
		else{
			$level = "Entry Data Daftar Haji";
		}
		$get_lastlogin = $this->m_master->get_last_login($USER_EMAIL);
		$this->data['level_info'] = "Anda Login Sebagai ". $level;
		$this->data['last_login'] = $get_lastlogin->USER_LAST_LOGIN;
		$this->data['meta_title'] = "Siskohat";
		$this->data['content'] = 'web/index';
		$samepass = $this->nativesession->get("SAMEPASS");
		if($samepass == 1) // validate
		{
			?>
				<script type="text/javascript">
			   	alert('Password and masih standar, segera ubah password anda!');
			   	</script>
		   	<?php 
		   	
		} 
        $this->load->view($this->template, $this->data);
	}

	public function get_kabkota(){
		$this->load->model('m_master');
		$KODE_PROP = $_POST ['KODE_PROP'];
		if ($KODE_PROP == 0) {
			$group1 = array ('--- Pilih KABUPATEN/KOTA ---');
			$kabkota = form_dropdown ( 'KODE_KABKOTA', $group1 );
			echo $kabkota;
		} else {
			$query_kabkota = $this->m_master->get_kabkota($KODE_PROP);
			$data ['dropdown_kabkota'] [] = "-- Pilih KABUPATEN/KOTA --";
			foreach ( $query_kabkota as $row ) {
				$data ['dropdown_kabkota'] [$row->KODE_KAB] = $row->NAMA_KABKOTA;
			}
			$group = form_dropdown('KODE_KABKOTA',$data['dropdown_kabkota']);
			echo $group;
		}
	}
	
	public function insert(){
		$this->data['meta_title']	= "Hajj Application System";
		$this->load->library('table');
		
		$ID_CABANG 		= $this->nativesession->get('ID_CABANG');
		$ID_PROPINSI 	= $this->nativesession->get('ID_PROVINSI');
		$get_record 	= $this->m_master->get_record($ID_CABANG);
		$get_propinsi 	= $this->m_master->getNamaPropinsi($ID_PROPINSI);
		$nama_propinsi 	= $get_propinsi->NAMA_PROPINSI;

		$TIPE_CABANG = $this->nativesession->get('TIPE_CABANG'); // jenis tabungan haji yang dibuka
		$msg = "";
		/*if($TIPE_CABANG == "S"){ // cabang syariah
			$msg = "Cabang Syariah, tabungan iB Baitullah";
		}
		else{
			$msg = "Cabang Transito, tabungan BNI HAJI";
		}*/
		$this->data['tabungan'] = $msg;
		
		dropdown(); // generate all dropdown needed
		$this->data ['optionKcp'] ['0'] = "-- Pilih Kabupaten / Kota --";
		$this->data['meta_title'] = "Input Calon Haji";
		$this->data['KODE_PROPINSI'] = $ID_PROPINSI;

		$this->data['dropdown_propinsi2'] = array(
			'' => "-- Pilih Propinsi --",
			$ID_PROPINSI => $nama_propinsi
		);

		$this->data['content'] = "web/insert_calhaj"; 	
		$this->load->view($this->template, $this->data);
	}

	public function insert_process(){
		$this->data['meta_title']	= "Hajj Application System";
		$ID_PROPINSI 	= $this->nativesession->get('ID_PROVINSI');
		
		$this->data['KODE_PROPINSI'] = $ID_PROPINSI;

		$get_propinsi 	= $this->m_master->getNamaPropinsi($ID_PROPINSI);
		$nama_propinsi 	= $get_propinsi->NAMA_PROPINSI;
		$this->data['dropdown_propinsi2'] = array(
			'' => "-- Pilih Propinsi --",
			$ID_PROPINSI => $nama_propinsi
		);

		$submit_value = $this->input->post('submit');
		if($submit_value == 'inquiry'){
			$data = array();
			$NO_REK = $this->input->post('NO_REK');

			if (!is_numeric($NO_REK)) {
			    $this->session->set_flashdata('message', 'Nomor Rekening Hanya Boleh Numerik');
				redirect('c_main/insert');
			}

			$json = file_get_contents(DB_REST_CONFIG.$NO_REK);
	        $obj = json_decode($json);	
	        foreach ($obj as $key => $value) {
	            $data[$key] = $value;
	        }

	        if(empty($data['errorCode'])){ // memastikan data ada

	        	$ID_PROPINSI = $this->nativesession->get('ID_PROVINSI');
				$get_propinsi = $this->m_master->getNamaPropinsi($ID_PROPINSI);
				$nama_propinsi = $get_propinsi->NAMA_PROPINSI;
	        	$depcode1 = $data['depcode1'];

	        	(strlen($data['namaJamaah']) > 40) ? $notifJamaah = "tidak boleh lebih 40 karakter" : $notifJamaah = "";
	        	$this->data['notifJamaah'] = $notifJamaah;

	        	(strlen($data['noKtp']) > 20) ? $notifKtp = "tidak boleh lebih 20 karakter" : $notifKtp = "";
	        	$this->data['notifKtp'] = $notifKtp;

	        	(strlen($data['tempatLahir']) > 25) ? $notifTmpt = "tidak boleh lebih 25 karakter" : $notifTmpt = "";
	        	$this->data['notifTmpt'] = $notifTmpt;

	        	(strlen($data['tanggalLahir']) > 8) ? $notifTgl = "tidak boleh lebih 8 karakter" : $notifTgl = "";
	        	$this->data['notifTgl'] = $notifTgl;

	        	(strlen($data['kodepos']) > 5) ? $notifKodepos = "tidak boleh lebih 5 karakter" : $notifKodepos = "";
	        	$this->data['notifKodepos'] = $notifKodepos;

	        	(strlen($data['namaDesa']) > 25) ? $notifDesa = "tidak boleh lebih 25 karakter" : $notifDesa = "";
	        	$this->data['notifDesa'] = $notifDesa;

	        	// (strlen($data['namaKecamatan'] > 25) ? $notifKcmtn = "tidak boleh lebih 25 karakter" : $notifKcmtn = "";
	        	// $this->data['notifKcmtn'] = $notifKcmtn;

	        	// (strlen($data['namaKabKota']) > 25)	? $notifKabkota = "tidak boleh lebih 25 karakter" : $notifKabkota = "";
	        	// $this->data['notifKabkota'] = $notifKabkota;

	        	dropdown(); // generate all dropdown needed
				$this->data ['optionKcp'] ['0'] = "-- Pilih Kabupaten / Kota --";
				$TIPE_CABANG = $this->nativesession->get('TIPE_CABANG');
				$flag_input = 0;

				$msg = "";

				if($depcode1 != "2941") {
					$msg = "Jenis Rekening yang digunakan harus IB BAITULLAH";
					$flag_input = 1;
				}
				
				/*if($TIPE_CABANG == "S" && $depcode1 == "2400"){ // cabang syariah
					$msg = "Anda Terdaftar sebagai Cabang Syariah, seharusnya tabungan yang dibuka adalah IB BAITULLAH bukan BNI TABUNGAN HAJI INDONESIA";
					$flag_input = 1;
				}
				else if($TIPE_CABANG == "S" && $depcode1 == "2941") {
					$msg = "Anda Terdaftar sebagai Cabang Syariah, tabungan yang dibuka sudah sesuai, yaitu IB BAITULLAH";
					$flag_input = 0;
				}
				else if($TIPE_CABANG == "T" && $depcode1 == "2941"){
					$msg = "Anda Terdaftar sebagai Cabang Transito, seharusnya tabungan yang dibuka adalah BNI TABUNGAN HAJI INDONESIA bukan IB BAITULLAH";	
					$flag_input = 1;
				}
				else if($TIPE_CABANG == "T" && $depcode1 == "2400"){
					$msg = "Anda Terdaftar sebagai Cabang Transito, tabungan yang dibuka sudah sesuai, yaitu BNI TABUNGAN HAJI INDONESIA";	
					$flag_input = 0;
				}
				else{
					$msg = "Terdapat kesalahan pembukaan rekening dengan depcode : ".$depcode1;		
					$flag_input = 1;
				}*/

				if($flag_input == 1){ // kondisi salah
					$this->data['NO_REK'] 			= "";
		        	$this->data['NAMA_JAMAAH'] 		= "";
		        	$this->data['NO_KTP'] 			= "";
		        	$this->data['MATA_UANG'] 		= "";
		        	$this->data['TMPT_LAHIR'] 		= "";
		        	$this->data['TGL_LAHIR'] 		= "";
		        	$this->data['JNS_KELAMIN'] 		= "";
		        	$this->data['ALAMAT'] 			= "";
		        	$this->data['KODEPOS'] 			= "";
		        	$this->data['NAMA_DESA'] 		= "";
		        	$this->data['NAMA_KECAMATAN'] 	= "";
		        	$this->data['NAMA_PROVINSI']	= "";
		        	$this->data['NAMA_KABKOTA'] 	= "";
		        	$this->data['PEKERJAAN'] 		= "";
		        	$this->data['PENDIDIKAN'] 		= "";
		        	$this->data['KODE_STATUS_NIKAH'] = "";

				}
				else{
					$this->data['NO_REK'] 			= $data['noRek'];
		        	$this->data['NAMA_JAMAAH'] 		= $data['namaJamaah']; 
		        	$this->data['NO_KTP'] 			= $data['noKtp'];
		        	$this->data['MATA_UANG'] 		= $data['mataUang'];
		        	$this->data['TMPT_LAHIR'] 		= $data['tempatLahir'];
		        	$this->data['TGL_LAHIR'] 		= $data['tanggalLahir'];
		        	$this->data['JNS_KELAMIN'] 		= $data['jenisKelamin'];
		        	$this->data['ALAMAT'] 			= $data['alamat'];
		        	$this->data['KODEPOS'] 			= $data['kodepos'];
		        	$this->data['NAMA_DESA'] 		= $data['namaDesa'];
		        	$this->data['NAMA_KECAMATAN'] 	= $data['namaKecamatan'];
		        	$this->data['NAMA_PROVINSI']	= $nama_propinsi;
		        	$this->data['NAMA_KABKOTA'] 	= $data['namaKabKota'];
		        	$this->data['PEKERJAAN'] 		= $data['pekerjaan'];
		        	$this->data['PENDIDIKAN'] 		= $data['pendidikan'];
		        	$this->data['KODE_STATUS_NIKAH'] = $data['statusNikah'];
				}

				$this->data['tabungan'] = $msg;

				$this->data['meta_title'] = "Input Calon Haji";
				$this->data['content'] = "web/insert_calhaj"; 	
				$this->load->view($this->template, $this->data);
	        }
	        else{
	        	$this->session->set_flashdata('message', $data['errorMessage']);
				redirect('c_main/insert');
	        }
			// sending nomor rekening ke java
		}	
		else if($submit_value == 'simpan'){
			$USER_EMAIL = $this->nativesession->get('USER_EMAIL');
			$NO_REK 	= (int) addslashes($this->input->post('NO_REK'));
			$get_rek 	= $this->m_master->select($NO_REK);
			if(!empty($get_rek)){ // validate data
				$ID_CABANG_RES 	= $get_rek->BRANCH_ID;
				$get_branch 	= $this->m_master->get_branch($ID_CABANG_RES);
				$branch_name 	= $get_branch->NAMA_CABANG;
				$this->session->set_flashdata('message', 'Data dengan rekening ' . $NO_REK . ' sudah terdaftar di '.$branch_name);
				redirect('c_main/insert');
			}

			$umur_nasabah = $this->input->post('umur_nasabah');
			if($umur_nasabah < 12){
				$this->session->set_flashdata('message', 'Jamaah dengan Usia dibawah 12 tahun tidak dapat didaftarkan');
				redirect('c_main/insert');
			}

			$provinsi_validate 		= $this->input->post('KODE_PROPINSI');
			$kabkota_validate 		= $this->input->post('KODE_KABKOTA');
			$pekerjaan_validate 	= $this->input->post('KODE_PEKERJAAN');
			$pendidikan_validate 	= $this->input->post('KODE_PENDIDIKAN');
			$status_nikah_validate 	= $this->input->post('KODE_STATUS_NIKAH');

			if(empty($provinsi_validate)){
				$this->session->set_flashdata('message', 'Provinsi Tidak Boleh Kosong');
				redirect('c_main/insert');
			}

			if(empty($kabkota_validate)){
				$this->session->set_flashdata('message', 'Kabupaten Kota Tidak Boleh Kosong');
				redirect('c_main/insert');
			}

			if(empty($pekerjaan_validate)){
				$this->session->set_flashdata('message', 'Pekerjaan Tidak Boleh Kosong');
				redirect('c_main/insert');
			}

			if(empty($pendidikan_validate)){
				$this->session->set_flashdata('message', 'Pendidikan Tidak Boleh Kosong');
				redirect('c_main/insert');
			}

			if(empty($status_nikah_validate)){
				$this->session->set_flashdata('message', 'Status Nikah Tidak Boleh Kosong');
				redirect('c_main/insert');
			}

			$this->load->library('form_validation');
			$this->form_validation->set_rules('NO_REK', 'NOMOR REKENING', 'required');
            $this->form_validation->set_rules('NAMA_JAMAAH', 'NAMA JAMAAH', 'required|max_length[40]');
            $this->form_validation->set_rules('JNS_JEMAAH', 'JENIS JEMAAH', 'required');
            $this->form_validation->set_rules('NO_KTP', 'NOMOR KTP', 'required|max_length[20]');
            $this->form_validation->set_rules('TMPT_LAHIR', 'TEMPAT LAHIR', 'required|max_length[25]');
            $this->form_validation->set_rules('TGL_LAHIR', 'TANGGAL LAHIR', 'required|max_length[8]');
            $this->form_validation->set_rules('JNS_KELAMIN', 'JENIS KELAMIN', 'required');
            $this->form_validation->set_rules('ALAMAT', 'ALAMAT', 'required|max_length[25]');
            $this->form_validation->set_rules('KODEPOS', 'KODEPOS', 'required|max_length[5]');
            $this->form_validation->set_rules('NAMA_DESA', 'NAMA DESA', 'required|max_length[25]');
            $this->form_validation->set_rules('NAMA_KECAMATAN', 'NAMA KECAMATAN', 'required|max_length[25]');
            $this->form_validation->set_rules('KODE_PROPINSI', 'PROVINSI', 'required');
            $this->form_validation->set_rules('KODE_STATUS_NIKAH', 'STATUS NIKAH', 'required');
			$this->form_validation->set_rules('NAMA_AYAH', 'NAMA AYAH', 'required');

            $KODE_KABKOTA		= $this->input->post('KODE_KABKOTA');
            $KODE_PEKERJAAN		= $this->input->post('KODE_PEKERJAAN');
            $KODE_PENDIDIKAN	= $this->input->post('KODE_PENDIDIKAN');

            if ($this->form_validation->run() == FALSE) {
                $this->load->library('table');
				$ID_CABANG = $this->nativesession->get('ID_CABANG');
				$get_record = $this->m_master->get_record($ID_CABANG);

				dropdown(); // generate all dropdown needed

				$this->data ['optionKcp'] ['0'] = "-- Pilih Kabupaten / Kota --";
				$this->data['meta_title'] = "Input Calon Haji";
				$this->data['content'] = "web/insert_calhaj"; 	
				$this->load->view($this->template, $this->data);
            }
            else {
                $NO_REK = (int)$NO_REK;
				$NAMA_JAMAAH = $this->input->post('NAMA_JAMAAH');
				$ID_PROVINSI = $this->nativesession->get('ID_PROVINSI');

				if(empty($NO_REK) or empty($NAMA_JAMAAH)){
					$this->session->set_flashdata ( 'message', 'Data Tidak boleh kosong' );
					redirect('c_main/insert');
				}

				$NAMA_JAMAAH = $this->input->post('NAMA_JAMAAH');
				$NOMOR_KTP = $this->input->post('NO_KTP');
				$TMPT_LAHIR = $this->input->post('TMPT_LAHIR');
				$TGL_LAHIR = $this->input->post('TGL_LAHIR');
				$ALAMAT = $this->input->post('ALAMAT');
				$KODEPOS = $this->input->post('KODEPOS');
				$NAMA_DESA = $this->input->post('NAMA_DESA');
				$NAMA_KECAMATAN = $this->input->post('NAMA_KECAMATAN');
				$NAMA_KABKOTA = $this->input->post('NAMA_KABKOTA');
				$NAMA_AYAH = $this->input->post('NAMA_AYAH');

				(strlen($NAMA_JAMAAH) > 40) ? $nmJamaah = substr($NAMA_JAMAAH, 0, 40) 	: $nmJamaah = $NAMA_JAMAAH;
				(strlen($NOMOR_KTP) > 20) 	? $noKtp = substr($NOMOR_KTP, 0, 20) 		: $noKtp = $NOMOR_KTP;
				(strlen($TMPT_LAHIR) > 25) 	? $tmptLahir = substr($TMPT_LAHIR, 0, 25) 	: $tmptLahir = $TMPT_LAHIR;
				(strlen($TGL_LAHIR) > 8) 	? $tglLahir = substr($TGL_LAHIR, 0, 8) 		: $tglLahir = $TGL_LAHIR;
				(strlen($ALAMAT) > 25) 		? $alamat = substr($ALAMAT, 0, 25) 			: $alamat = $ALAMAT;
				(strlen($KODEPOS) > 5) 		? $kodepos = substr($KODEPOS, 0, 5) 		: $kodepos = $KODEPOS;
				(strlen($NAMA_DESA) > 25) 	? $nmDesa = substr($NAMA_DESA, 0, 25) 		: $nmDesa = $NAMA_DESA;
				(strlen($NAMA_KECAMATAN) > 25) ? $nmKecamatan = substr($NAMA_KECAMATAN, 0, 25) : $nmKecamatan = $NAMA_KECAMATAN;
				(strlen($NAMA_KABKOTA) > 25)   ? $kabkota = substr($NAMA_KABKOTA, 0, 25) : $kabkota = $NAMA_KABKOTA;
				(strlen($NAMA_AYAH) > 40) ? $nmAyah = substr($NAMA_AYAH, 0, 40) : $nmAyah = $NAMA_AYAH;
				
				$ID_CABANG = $this->nativesession->get('ID_CABANG');
				
				//str_replace("'", "''", $value)
				$nmJamaah = str_replace("'", "''", $nmJamaah);
				// $nmAyah = str_replace("'", "'", $nmAyah);
				$KODE_KABKOTA = $this->input->post('KODE_KABKOTA');

				// get nama kab kota
				$get_nama_kabkota = $this->m_master->get_nama_kabupaten($ID_PROVINSI, $KODE_KABKOTA);
				$nama_kabkota_new = $get_nama_kabkota->NAMA_KABKOTA;
				$nmKecamatan = trim(str_replace("/", "", $nmKecamatan));
				
				$data = array(
					'NO_REK' => "'".$NO_REK."'",
					'NAMA_JAMAAH' => "'".$nmJamaah."'",
		 			'JENIS_JAMAAH' => "'".$this->input->post('JNS_JEMAAH')."'",
					'NO_KTP' => "'".$noKtp."'",
					'TMPT_LAHIR' => "'".oracle_escape_string($tmptLahir)."'",
					'TGL_LAHIR' => "TO_DATE('".$tglLahir."', 'ddMMyyyy')",
					'JNS_KELAMIN' => "'".$this->input->post('JNS_KELAMIN')."'",
					'ALAMAT' => "'".oracle_escape_string($alamat)."'",
					'KODEPOS' => "'".$kodepos."'",
					'NAMA_DESA' => "'".oracle_escape_string($nmDesa)."'",
					'NAMA_KECAMATAN' => "'".oracle_escape_string($nmKecamatan)."'",
					'NAMA_KABKOTA' => "'".oracle_escape_string($nama_kabkota_new)."'",
					'KODE_PROVINSI' => "'".$this->input->post('KODE_PROPINSI')."'",
					'KODE_KABKOTA' => "'".$this->input->post('KODE_KABKOTA')."'",
					'KODE_PEKERJAAN' => "'".$this->input->post('KODE_PEKERJAAN')."'",
					'KODE_PENDIDIKAN' => "'".$this->input->post('KODE_PENDIDIKAN')."'",
					'KODE_STATUS_NIKAH' => "'".$this->input->post('KODE_STATUS_NIKAH')."'",
					'MATA_UANG' => "'".$this->input->post('MATA_UANG')."'",
					'DATE_INSERT' => "TO_DATE('".get_date_now()."', 'yyyy/mm/dd hh24:mi:ss')",
					'BRANCH_ID' => "'".$ID_CABANG."'",
					'TIPE_CABANG' => "'".$this->nativesession->get('TIPE_CABANG')."'",
					'USER_INSERT' => "'".$this->nativesession->get('USER_EMAIL')."'",
					'NAMA_AYAH' => "'".oracle_escape_string($nmAyah)."'"
				);
				$column = "ID, NO_REK, NAMA_JAMAAH, JENIS_JAMAAH, NO_KTP, TMPT_LAHIR, TGL_LAHIR, JNS_KELAMIN, ALAMAT, KODEPOS, NAMA_DESA, NAMA_KECAMATAN, NAMA_KABKOTA, KODE_PROVINSI, KODE_KABKOTA, KODE_PEKERJAAN, KODE_PENDIDIKAN, KODE_STATUS_NIKAH, MATA_UANG, DATE_INSERT, BRANCH_ID, TIPE_CABANG, USER_INSERT, NAMA_AYAH";
				$nextval = $this->m_master->skh_seq_calhaj()->NEXTVAL;
				$a = insert_loop('SKH_CALHAJ', $column, $nextval, $data);
				
				if($this->m_master->insert_query($a)) { // validate insert
					//  gagal insert
					$this->session->set_flashdata('message', 'Gagal insert, cek kembali isian anda');
					$this->data['content'] = "web/insert_calhaj"; 	
					$this->load->view($this->template, $this->data);
				} else {
					// berhasil insert
					$this->session->set_flashdata ( 'message', 'Data Berhasil di input' );
					redirect('c_main/insert');
				}				
            }
		}
	}

	public function search_norek($norek){
		$this->data['meta_title']	= "Hajj Application System";
		$select['NO_REK'] = $norek;
        $norek_data = $this->m_master->select($norek);

        if(empty($norek_data)){
			$arr = array('errorCode' => '999', 'errorMessage' => '0108', '');
            $data =  json_encode($arr);
            header('Content-Type: application/json');
            header('HTTP/1.1: 200');
            header('Status: 200');
            header('Content-Length: '.strlen($data));
            exit($data);
        }
        else{
        	$ids = array();
	        foreach($norek_data as $permissionObject){
	            $ids[] = $permissionObject->ID;
	            $ids[] = $permissionObject->NAMA_JAMAAH;
	            $ids[] = $permissionObject->JENIS_JAMAAH;
	            $ids[] = $permissionObject->NO_KTP;
	            $ids[] = $permissionObject->TMPT_LAHIR;
	            $ids[] = $permissionObject->TGL_LAHIR;
	            $ids[] = $permissionObject->JNS_KELAMIN;
	            $ids[] = $permissionObject->ALAMAT;
	            $ids[] = $permissionObject->KODEPOS;
	            $ids[] = $permissionObject->NAMA_DESA;
	            $ids[] = $permissionObject->NAMA_KECAMATAN;
	            $ids[] = $permissionObject->NAMA_KABKOTA;
	            $ids[] = $permissionObject->KODE_PROVINSI;
	            $ids[] = $permissionObject->KODE_KABKOTA;
	            $ids[] = $permissionObject->KODE_PEKERJAAN;
	            $ids[] = $permissionObject->KODE_PENDIDIKAN;
	            $ids[] = $permissionObject->KODE_STATUS_NIKAH;
	            $ids[] = $permissionObject->NO_REK;
	            $ids[] = $permissionObject->MATA_UANG;
	            $ids[] = $permissionObject->BRANCH_ID;
	            $ids[] = $permissionObject->TIPE_CABANG;
	        }

	        $data = array(
	            'id' => $ids[0],
	            'namaJamaah' => $ids[1],
	            'jenisJamaah' => $ids[2],
	            'noKtp' => $ids[3],
	            'tmptLahir' => $ids[4],
	            'tglLahir' => $ids[5],
	            'jnsKelamin' => $ids[6],
	            'alamat' => $ids[7],
	            'kodepos' => $ids[8],
	            'namaDesa' => $ids[9],
	            'namaKecamatan' => $ids[10],
	            'namaKabkota' => $ids[11],
	            'kodeProvinsi' => $ids[12],
	            'kodeKabkota' => $ids[13],
	            'kodePekerjaan' => $ids[14],
	            'kodePendidikan' => $ids[15],
	            'kodeStatusNikah' => $ids[16],
	            'noRek' => $ids[17],
	            'mataUang' => $ids[18],
	            'branchId' => $ids[19],
	            'tipeCabang' => $ids[20],
	        );

	        // echo json_encode($data);
	        $data = str_replace(array('[', ']'), '', htmlspecialchars(json_encode($data), ENT_NOQUOTES));
	        // echo $data;
	        header('Content-Type: application/json');
	        header('HTTP/1.1: 200');
	        header('Status: 200');
	        header('Content-Length: '.strlen($data));
	        exit($data);
        }
	}

	public function inquiry(){
		$this->data['meta_title']	= "Hajj Application System";
		$this->data['content'] = "web/inquiry";
		$this->load->view($this->template, $this->data);
	}

	public function inquiry_result(){
		$this->data['meta_title']	= "Hajj Application System";
		$this->data['disp'] = "none";
		$ID_CABANG = $this->nativesession->get('ID_CABANG');
		$ID_LEVEL = $this->nativesession->get('ID_LEVEL');

		$NO_REK 	= addslashes($this->input->post('NO_REK'));
		$NO_REK 	= preg_replace('/[^\w]/', '', $NO_REK);
		$validate 	= is_numeric($NO_REK);

		if(empty($NO_REK)){
			redirect('c_main/inquiry');
		}

		$res = $this->m_master->select($NO_REK);

		if(!empty($res)){
			if($ID_LEVEL == 2){
				$ID_CABANG_RES = $res->BRANCH_ID;
				if($ID_CABANG != $ID_CABANG_RES){
					$get_branch = $this->m_master->get_branch($ID_CABANG_RES);
					$branch_name = $get_branch->NAMA_CABANG;
					$this->data['message'] = "Nomor rekening $NO_REK sudah didaftarkan di cabang ".$branch_name;
				}
				else{
					$this->data['disp'] = "";
					$this->data['NO_REK'] = $res->NO_REK;
					$this->data['JENIS_JAMAAH'] = $res->JENIS_JAMAAH;
		        	$this->data['NAMA_JAMAAH'] = $res->NAMA_JAMAAH;
		        	$this->data['NO_KTP'] = $res->NO_KTP;
		        	$this->data['MATA_UANG'] = $res->MATA_UANG;
		        	$this->data['TMPT_LAHIR'] = $res->TMPT_LAHIR;
		        	$this->data['TGL_LAHIR'] = $res->TGL_LAHIR;
		        	$this->data['JNS_KELAMIN'] = $res->JNS_KELAMIN;
		        	$this->data['ALAMAT'] = $res->ALAMAT;
		        	$this->data['KODEPOS'] = $res->KODEPOS;
		        	$this->data['NAMA_DESA'] = $res->NAMA_DESA;
		        	$this->data['NAMA_KECAMATAN'] = $res->NAMA_KECAMATAN;
		        	$this->data['KODE_PROVINSI'] = $res->KODE_PROVINSI;
		        	$this->data['NAMA_KABKOTA'] = $res->NAMA_KABKOTA;
		        	$this->data['PEKERJAAN'] = $res->KODE_PEKERJAAN;
		        	$this->data['PENDIDIKAN'] = $res->KODE_PENDIDIKAN;
		        	$this->data['KODE_STATUS_NIKAH'] = $res->KODE_STATUS_NIKAH;
					$this->data['NAMA_AYAH'] = $res->NAMA_AYAH;
				}
			}
			else{
				$this->data['disp'] = "";
				$this->data['NO_REK'] = $res->NO_REK;
				$this->data['JENIS_JAMAAH'] = $res->JENIS_JAMAAH;
	        	$this->data['NAMA_JAMAAH'] = $res->NAMA_JAMAAH;
	        	$this->data['NO_KTP'] = $res->NO_KTP;
	        	$this->data['MATA_UANG'] = $res->MATA_UANG;
	        	$this->data['TMPT_LAHIR'] = $res->TMPT_LAHIR;
	        	$this->data['TGL_LAHIR'] = $res->TGL_LAHIR;
	        	$this->data['JNS_KELAMIN'] = $res->JNS_KELAMIN;
	        	$this->data['ALAMAT'] = $res->ALAMAT;
	        	$this->data['KODEPOS'] = $res->KODEPOS;
	        	$this->data['NAMA_DESA'] = $res->NAMA_DESA;
	        	$this->data['NAMA_KECAMATAN'] = $res->NAMA_KECAMATAN;
	        	$this->data['KODE_PROVINSI'] = $res->KODE_PROVINSI;
	        	$this->data['NAMA_KABKOTA'] = $res->NAMA_KABKOTA;
	        	$this->data['PEKERJAAN'] = $res->KODE_PEKERJAAN;
	        	$this->data['PENDIDIKAN'] = $res->KODE_PENDIDIKAN;
	        	$this->data['KODE_STATUS_NIKAH'] = $res->KODE_STATUS_NIKAH;
				$this->data['NAMA_AYAH'] = $res->NAMA_AYAH;
			}
		}
		else{
			$this->data['message'] = "Tidak ditemukan data dengan nomor rekening : ". $NO_REK;
		}

		$this->data['content'] = "web/inquiry_result";
		$this->load->view($this->template, $this->data);
	}

	public function update_data($NO_REK=null){
		$this->data['meta_title']	= "Update Data";
		$NO_REK = $this->input->post('NO_REK');
		if(empty($NO_REK)){
			$NO_REK = $this->uri->segment(3);

			if(empty($NO_REK)){
				redirect('C_main/inquiry');
			}
		}

		$get_data = $this->m_master->get_data_update($NO_REK);

		$this->data['NO_REK'] = $get_data->NO_REK;
		$this->data['ALAMAT'] = $get_data->ALAMAT;
		$this->data['KODE_KABKOTA_UPD'] = $get_data->KODE_KABKOTA;
		$this->data['KODE_STATUS_NIKAH_UPD'] = $get_data->KODE_STATUS_NIKAH;
		$this->data['KODE_PENDIDIKAN_UPD'] = $get_data->KODE_PENDIDIKAN;
		$this->data['KODE_PEKERJAAN_UPD'] = $get_data->KODE_PEKERJAAN;
		$this->data['NAMA_DESA'] = $get_data->NAMA_DESA;
		$this->data['NAMA_KECAMATAN'] = $get_data->NAMA_KECAMATAN;
		$this->data['NAMA_AYAH'] = $get_data->NAMA_AYAH;

		dropdown();

		$this->data['content'] = "web/admin/update_data";
		$this->load->view($this->template, $this->data);
	}

	public function update(){
		$this->data['meta_title']	= "Hajj Application System";
		$NO_REK = $this->input->post('NO_REK');

		$this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');

        $this->form_validation->set_rules('ALAMAT', 'alamat', 'required');
        $this->form_validation->set_rules('NAMA_DESA', 'desa', 'required');
        $this->form_validation->set_rules('NAMA_KECAMATAN', 'kecamatan', 'required');
        $this->form_validation->set_rules('KODE_KABKOTA', 'kabupatenkota', 'required');
        $this->form_validation->set_rules('KODE_PEKERJAAN', 'pekerjaan', 'required');
        $this->form_validation->set_rules('KODE_PENDIDIKAN', 'pendidikan', 'required');
        $this->form_validation->set_rules('KODE_STATUS_NIKAH', 'status', 'required');
		$this->form_validation->set_rules('NAMA_AYAH', 'nama ayah', 'required');

        if ($this->form_validation->run() == FALSE)
        {
			//redirect('C_main/inquiry');
			$this->session->set_flashdata('message_success', 'Gagal update, Data tidak lengkap');
			redirect('C_main/update_data/'.$NO_REK);
        }
        else
        {
        	$NO_REK = $this->input->post('NO_REK');
        	$ID_PROVINSI = $this->nativesession->get('ID_PROVINSI');
        	$KODE_KAB = $this->input->post('KODE_KABKOTA');

        	$get_nama_kabupaten = $this->m_master->get_nama_kabupaten($ID_PROVINSI, $KODE_KAB);
        	$nama_kabupaten = $get_nama_kabupaten->NAMA_KABKOTA;

			$data = array(
				'ALAMAT' => oracle_escape_string($this->input->post('ALAMAT')),
				'NAMA_DESA' => oracle_escape_string($this->input->post('NAMA_DESA')),
				'NAMA_KECAMATAN' => oracle_escape_string($this->input->post('NAMA_KECAMATAN')),
				'KODE_KABKOTA' => $this->input->post('KODE_KABKOTA'),
				'KODE_PEKERJAAN' => $this->input->post('KODE_PEKERJAAN'),
				'KODE_PENDIDIKAN' => $this->input->post('KODE_PENDIDIKAN'),
				'KODE_STATUS_NIKAH' => $this->input->post('KODE_STATUS_NIKAH'),
				'NAMA_KABKOTA' => $nama_kabupaten,
				'NAMA_AYAH' => oracle_escape_string($this->input->post('NAMA_AYAH')),
			);
			$this->db->update('SKH_CALHAJ', $data, array('NO_REK' => $NO_REK));
			$this->session->set_flashdata('message_success', 'Data dengan nomor rekening : '.$NO_REK.' berhasil di update');
			redirect('C_main/inquiry');
        }
	}

	public function delete(){
		$this->data['meta_title']	= "Delete Data";
		$this->data['form_action'] 	= "C_main/delete_inq";
		$this->data['content'] 		= "web/admin/delete_index";
		$this->load->view($this->template, $this->data);
	}

	public function delete_inq(){
		$NO_REK 	= (int) addslashes(trim($this->input->post('NO_REK'))); // validate no rek int
		if(empty($NO_REK)){ // validasi nomor rekening
			$this->session->set_flashdata('err_msg', "Nomor Rekening Tidak Boleh Kosong / Harus Numerik");
			redirect('C_main/delete');
		}
		$NO_REK = htmlentities($NO_REK); // prevent special char dan xss filtering
		$get_data = $this->m_master->get_rekening($NO_REK); // get data based on no rek
		if(!empty($get_data)){
			$template = table_tmpl(); // set template table
			$this->table->set_template($template);
			$this->table->set_heading('NOMOR REKENING', 'NAMA JAMAAH', 'JENIS KELAMIN', 'NO KTP', 'TGL LAHIR', 'USER INSERT', 'NAMA CABANG');
			foreach ($get_data as $row) {
				$this->table->add_row($row->NO_REK, $row->NAMA_JAMAAH, $row->JNS_KELAMIN, $row->NO_KTP, $row->TGL_LAHIR, $row->USER_INSERT, $row->NAMA_CABANG);
			}
			$this->data['table'] = $this->table->generate();
			$get_rek = $this->m_master->get_rekening_row($NO_REK);
			$this->data['old_record'] = $get_rek->NO_REK." | ".$get_rek->NAMA_JAMAAH." | ".$get_rek->JNS_KELAMIN." | ".$get_rek->NO_KTP." | ".$get_rek->TGL_LAHIR." | ".$get_rek->USER_INSERT." | ".$get_rek->NAMA_CABANG;
		}
		else{
			$this->data['table'] 	= "";
			$this->data['msg_err'] 	= "Data Tidak Ditemukan";
			$this->session->set_flashdata('err_msg', "Nomor Rekening Tidak Ditemukan");
			redirect('C_main/delete');
		}

		$this->data['meta_title']	= "Delete Data";
		$this->data['form_action'] 	= "C_main/delete_data";
		$this->data['content'] 		= "web/admin/delete_form";
		$this->data['NO_REK']		= $NO_REK;
		$this->load->view($this->template, $this->data);
	}

	public function delete_data(){
		$NO_REK 	 = $this->input->post('NO_REK');
		$REASON 	 = $this->input->post('REASON');
		$BASED_ON 	 = $this->input->post('BASED_ON');
		$DATA_OLD  	 = $this->input->post('OLD_RECORD');
		$TGL_DELETE  = get_date_now();
		$IP_ADDRESS  = $_SERVER['REMOTE_ADDR'];
		$USER_DELETE = $this->nativesession->get('USER_EMAIL');
		/// validate nomor rekening tidak boleh kosong
		if(empty($NO_REK)){
			$this->session->flashdata('err_msg', 'Nomor Rekening Tidak Terdaftar Gagal Delete');
			redirect('C_main/delete');
		}
		/// form validation codeigniter
        $this->load->library('form_validation');
        $this->form_validation->set_rules('REASON', 'ALASAN DELETE', 'required');
        $this->form_validation->set_rules('BASED_ON', 'DASAR DELETE', 'required');
        /// form validation codeigniter
        if ($this->form_validation->run() == FALSE) {
        	$this->session->set_flashdata('err_msg', 'ALASAN / DASAR Delete data Nomor Rekening : '. $NO_REK .' Harus Diisi');
            redirect('C_main/delete');
        }
        else {
            // insert data
			$this->insertclob->insert($NO_REK, $REASON, $BASED_ON, $DATA_OLD, $TGL_DELETE, $IP_ADDRESS, $USER_DELETE);
			// delete data
			$this->m_master->delete_data('SKH_CALHAJ', 'NO_REK', $NO_REK);
			// redirect page
			$this->session->set_flashdata('success_msg', "Data dengan Nomor Rekening : ". $NO_REK ." Berhasil di Delete");
			redirect('C_main/delete');
        }
	}

	public function update_password(){
    	$this->data['title'] = "Ubah Password";
    	$this->data['meta_title'] = "Form Ubah Password";
    	$this->data['content'] = "web/users/ubah_password";
    	$this->load->view($this->templates, $this->data);
    }

    public function ubahPasswordProses(){
    	$this->load->library('SimpleLoginSecure');
    	$old_password = $this->input->post('old_password');
    	$new_password = $this->input->post('new_password');
    	$password_conf = $this->input->post('password_conf');
    	// $user_id = $this->input->post('id');
    	$user_id = $this->session->userdata('user');

    	if($new_password != $password_conf){
    		$this->session->set_flashdata("message","Maaf Password Lama Anda Salah");
    	}else{
    		$update = $this->simpleloginsecure->edit_password($user_id,$old_password,$new_password);
    		if($update){
    			$this->session->set_flashdata("message","Password Berhasil di Update");
    		}else $this->session->set_flashdata("message","Password Gagal di Update");
    	}
    	redirect('c_main/update_password');
    }
	
	public function data_rekon(){

    	$this->data['meta_title'] 	= "Data Calon Haji";
		$this->data['content'] 		= "web/datahaji_index"; 	
		$this->data['table']		= "";

		$this->data['month'] = array('' => ' -- Pilih Bulan -- ', '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mai', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');

		$base_year	= date('Y');
		$start_year = $base_year;
		$end_year	= $start_year + 5;
		$year_list	= array();
		for( $i = $start_year; $i <= $end_year; $i++){   
			$year_list[] = $i;
		} 

		$options = array('' => '-- Pilih Tahun --');
		foreach ($year_list as $value) {
           	$options[$value] = $value;
        }
        $this->data['year'] = $options; 
        $this->data['val_search']	= "";
        $this->data['visible'] = "hidden";

    	$this->load->view($this->template, $this->data);

    }

    public function get_datarekon(){

    	// $ID_CABANG 				= $this->nativesession->get('ID_CABANG');

    	$KODE_CABANG 			= $this->nativesession->get('KODE_CABANG');
    	$this->data['table']	= "";
		$this->data['month'] 	= array('' => ' -- Pilih Bulan -- ', '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mai', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');

		$base_year	= date('Y');
		$start_year = $base_year;
		$end_year	= $start_year + 5;
		$year_list	= array();
		for( $i = $start_year; $i <= $end_year; $i++){   
			$year_list[] = $i;
		} 

		$options = array('' => '-- Pilih Tahun --');
		foreach ($year_list as $value) {
           	$options[$value] = $value;
        }
        $this->data['year'] = $options; 
    	
    	$month 	= $this->input->post('month');
    	$year 	= $this->input->post('year');

    	if($month == "" || $year == ""){
    		$this->session->set_flashdata("message","Data Bulan dan Tahun tidak boleh kosong");
    		redirect('c_main/data_rekon');
    	}

    	$search_param = $month."/".$year;
    	$inq = $this->m_master->hajj_inq($search_param, $KODE_CABANG);

    	if($inq->num_rows() > 0){

    		$res_inq 	= $inq->result();
			$template 	= table_tmpl(); // set template table
			$this->table->set_template($template);
			$this->table->set_heading('TGl TRANSAKSI', 'NARASI', 'TGL DAFTAR', 'NO REK', 'NO VALIDASI', 'NAMA REK', 'NAMA DEPAG', 'NOMINAL', 'KOTA', 'KODE CABANG');
    		foreach ($res_inq as $row) {
    			$this->table->add_row($row->TGL_TRX, $row->NARASI, $row->TANGGAL_DAFTAR, $row->NO_REKENING, $row->NO_VALIDASI, $row->NAMA_RK, $row->NAMA_DEPAG, $row->NOMINAL, $row->KOTA, $row->KODE_CABANG);	
    		}
    		$this->data['table'] = $this->table->generate();
    		$this->data['visible'] = "visible";

    	} else {
    		$this->data['visible'] = "hidden";
			$this->data['table'] = '';    		
    		$this->session->set_flashdata("message","Tidak Ada Data");
    		redirect('c_main/data_rekon');
    	}

    	$this->data['val_search']	= $search_param;

		$this->data['meta_title'] 	= "Data Calon Haji";
		$this->data['content'] 		= "web/datahaji_index"; 	

    	$this->load->view($this->template, $this->data);

    }

    public function export($month, $year){

    	$KODE_CABANG 	= $this->nativesession->get('KODE_CABANG');
    	$search_param 	= $month."/".$year;
    	$inq 			= $this->m_master->hajj_inq($search_param, $KODE_CABANG);

    	$dataFormatName = date("Y_m_d");

    	$columnHeader ='';
		$columnHeader = "TGl TRANSAKSI"."\t"."NARASI"."\t"."TGL DAFTAR"."\t"."NO REK"."\t"."NO VALIDASI"."\t"."NAMA REK"."\t"."NAMA DEPAG"."\t"."NOMINAL"."\t"."KOTA"."\t"."KODE CABANG"."\t";

		$setData='';

		if($inq->num_rows() > 0){
			$rowData 	= '';
			$res_inq 	= $inq->result();
    		foreach ($res_inq as $row) {
    			$value = '"' . $row->TGL_TRX . '"' . "\t" . '"' . $row->NARASI . '"' . "\t" . '"' . $row->TANGGAL_DAFTAR . '"' . "\t" . '"' . $row->NO_REKENING."'" . '"' . "\t" . '"' . $row->NO_VALIDASI."'" . '"' . "\t" . '"' . $row->NAMA_RK . '"' . "\t" . '"' . $row->NAMA_DEPAG . '"' . "\t" . '"' . $row->NOMINAL . '"' . "\t" . '"' . $row->KOTA . '"' . "\t" . '"' . $row->KODE_CABANG . '"' . "\n";
    			$rowData .= $value;
    		}
    		$setData .= trim($rowData)."\n";
		}

		header("Pragma: public");
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename=datarekon_".$dataFormatName.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo ucwords($columnHeader)."\n".$setData."\n";
    }

   
  	public function management_ppiu()
  	{
  		$this->data['title'] = "Management PPIU";
    	$this->data['meta_title']	= "Hajj Application System";
    	$this->data['content'] = "web/list_siskopatuh";
    	$this->load->view('template', $this->data);
    }

  /*  public function dtManagementPPPIU()
    {
    	if($_SERVER['REQUEST_METHOD'] === 'GET')
    	{
    		$this->load->model("M_Siskopatuh");
    		$data = $this->M_Siskopatuh->get_all_data();
    		echo json_encode($data);
    	}
    }*/

   public function dtManagementPPPIU()
    {
    	if ($_SERVER['REQUEST_METHOD'] === 'GET') 
    	{
    		$this->load->model("M_Siskopatuh");

    		$data = $this->M_Siskopatuh->get_all_data();

    		if ($data) 
    		{
    			# data found
    			$temp = array();
    			foreach ($data as $row) {
    				$temp[] = array(
    					0 => $row->KD_PPIU,
    					1 => $row->NAMA_PPIU,
    					2 => $row->REKENING_PENAMPUNGAN,
    					3 => $row->REKENING_OPERASIONAL,
    					4 => '<a href="edit_data" class="btn btn-info btn-sm editRecord" id="'.$row->KD_PPIU.'">EDIT</a>
    						<a href="#" onclick="showDeleteModal('.$row->KD_PPIU.')"  class=".openModal" id="show_delete_modal" >HAPUS</a>'
    				);
    			}

    			$response['data'] = $temp;
    			echo json_encode($response);
    		}
		}
    }

    public function add_data_siskopatuh()
    {
    	$this->data['meta_title'] = "Hajj Application System";
    	$this->data['content'] = "web/add_data_siskopatuh";
    	$this->load->library('table');

    	$CI=& get_instance();
    	$this->load->model('M_Siskopatuh');

    	$this->data['siskopatuh'] = $CI->M_Siskopatuh->save();
    	
    	$this->load->view($this->template, $this->data);

    	//save data to datatable
    	$KD_PPIU=$this->input->post('KD_PPIU');
        $NAMA_PPIU=$this->input->post('NAMA_PPIU');
        $REKENING_PENAMPUNGAN=$this->input->post('REKENING_PENAMPUNGAN');
        $REKENING_OPERASIONAL=$this->input->post('REKENING_OPERASIONAL');
        $data=$this->m_barang->simpan_barang($KD_PPIU, $NAMA_PPIU, $REKENING_PENAMPUNGAN, $REKENING_OPERASIONAL);
        echo json_encode($data);
    }

    /*public function delete_data_siskopatuh(){
		$this->data['meta_title']	= "Delete Data";
		$this->data['form_action'] 	= "C_main/delete_inq";
		$this->data['content'] 		= "web/admin/delete_index";
		$this->load->view($this->template, $this->data);
	}*/

   /* public function ajax_delete($KD_PPIU)
    {	
    	if($KD_PPIU)
    		return $this->M_Siskopatuh->delete_by_kode_ppiu($KD_PPIU);
    	return false;
    	
    	//echo json_encode(array('status' => TRUE));
    }
*/

 /*  public function showDeleteModal(val)
    {
    	$KD_PPIU = $this->input->post('KD_PPIU');
    	$data = $this->M_Siskopatuh->delete_data('SISKOPATUH_REKENING_PPIU', $KD_PPIU);
    	redirect('c_main/management_ppiu');
    }*/
    public function haiDelete()
    {
    	$this->load->helper('url');
    	echo "Hai Bambankkkkkkkkkk";
    }

    public function edit_data()
    {
    	$this->load->helper('url');
    	$this->load->model('M_Siskopatuh');
    	$data= $this->M_Siskopatuh->updateEmp();
    	echo json_encode($data);
    	$this->load->view('web/admin/edit_data_siskopatuh');
    }

   

}
