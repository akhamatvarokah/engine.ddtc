﻿<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peraturan_pajak extends My_Controller {

	public function __construct()
	{
    	parent::__construct();

    	$this->load->model('user_model');
    	$this->load->model('jenis_dokumen_model');
    	$this->load->model('kelompok_model');
    	$this->load->model('regulasi_pajak_model');
    	$this->load->model('favourite_model');
    	$this->load->model('lastseen_model');
    	$this->load->model('topik_model');

    	$this->load->model('p3b_article_model');
    	$this->load->model('putusan_pengadilan_model');
    	$this->load->model('putusan_ma_model');

    	$this->load->library('facebook');
    	$this->load->library('pagination');
		$this->load->library('session');

    	$this->load->helper('peraturan_pajak_helper');
	}

	public function noc()
	{	
		//$path = file_get_contents('http://server.ddtc.co.id/convertpdf/?url=http://engine.ddtc.co.id/cetak/noc/12441/');
		//$path = file_get_contents('http://engine.ddtc.co.id/assets/download/peraturanpajak/lampiran/wt_9663.pdf');
		//$filename = 'tes123.pdf';
		//force_download($filename, $path);
		//echo $path;
		//var_dump(base_url());
		redirect('home');
	}

	public function update_url($page)
	{
		$no = 0;
		$perpage = 100;

		$pp = $this->regulasi_pajak_model->get_test_perpage($page, $perpage);

		foreach($pp as $row)
		{
			$id = $row['id'];
			$nomordokumen = $row['nomordokumen'];
			$jenis_dokumen_lengkap = $row['jenis_dokumen_lengkap'];

			$permalink = str_replace('/', ' ',$nomordokumen);
            $permalink = str_replace('.', ' ',$nomordokumen);
            $permalink = $jenis_dokumen_lengkap.' '.$permalink;
            $permalink = url_title($permalink, '-', TRUE);

            $data = array('permalink' => $permalink);

            $update = $this->regulasi_pajak_model->update($id, $data);

            if($update)
            {
            	$no++;
            }
        }

        echo $no;
	}

	public function change_table($page)
	{
		//db1 = regulasi_pajak
		//db2 = peraturan_pajak
		$perpage = 50;
		$start = ($page-1)*$perpage;

		$this->db->limit($perpage, $start);
		$db1 = $this->db->get('regulasi_pajak')->result_array();

		foreach($db1 as $row)
		{
			$id = $row['id'];
			$kelompok = $row['kelompok'];
			$jenisdok2 = $row['jenisdok2'];
			$jenis_dokumen_lengkap = $row['jenis_dokumen_lengkap'];
			$nomordokumen = $row['nomordokumen'];
			$permalink = $row['permalink'];
			$nomor = $row['nomor'];
			$tahun = $row['tahun'];
			$tanggal = $row['tanggal'];
			$perihal = $row['perihal'];
			$body_final = $row['body_final'];
			$lamp1_filename = $row['lamp1_filename'];
			$topik = $row['topik'];
			$regstatus = $row['regstatus'];
			$view = $row['view'];
			$sync = $row['sync'];
			$id_tkb = $row['id_tkb'];
			$id_tr = $row['id_tr'];
			$id_bc = $row['id_bc'];
			$id_dj = $row['id_dj'];
			$id_jdi = $row['id_jdi'];
			$id_o = $row['id_o'];
			$id_tf = $row['id_tf'];
			$publish = $row['publish'];
			$unpublish_reason = $row['unpublish_reason'];
			$reviewed = $row['reviewed'];
			$submit_date = $row['submit_date'];

			//get db2 data
			$this->db->where('id_o', $id_o);
			$db2 = $this->db->get('peraturan_pajak')->row_array();
			//------------

			$status_dokumen = $db2['status_dokumen'];
			$linklist = $db2['linklist'];
			$statuslist = $db2['statuslist'];
			$historylist = $db2['historylist'];

			$data = array(
					'id' 					=> $id,
					'kelompok'				=> $kelompok,
					'jenisdok2'				=> $jenisdok2,
					'jenis_dokumen_lengkap'	=> $jenis_dokumen_lengkap,
					'nomordokumen'			=> $nomordokumen,
					'permalink'				=> $permalink,
					'nomor'					=> $nomor,
					'tahun'					=> $tahun,
					'tanggal'				=> $tanggal,
					'perihal'				=> $perihal,
					'body_final'			=> $body_final,
					'lamp1_filename'		=> $lamp1_filename,
					'topik'					=> $topik,
					'regstatus'				=> $regstatus,
					'view'					=> $view,
					'status_dokumen'		=> $status_dokumen,
					'linklist'				=> $linklist,
					'statuslist'			=> $statuslist,
					'historylist'			=> $historylist,
					'sync'					=> $sync,
					'id_tkb'				=> $id_tkb,
					'id_tr'					=> $id_tr,
					'id_bc'					=> $id_bc,
					'id_dj'					=> $id_dj,
					'id_jdi'				=> $id_jdi,
					'id_o'					=> $id_o,
					'id_tf'					=> $id_tf,
					'publish'				=> $publish,
					'unpublish_reason'		=> $unpublish_reason,
					'reviewed'				=> $reviewed,
					'submit_date'			=> $submit_date
				);
			
			$this->db->insert('peraturan_pajak_new' ,$data);
		}
	}

	public function index()
	{
		/*PP searchbox*/
        $ls_topik = $this->topik_model->get_all_publish_order_by('topik_id', 'asc');
        //$ls_jenis_dokumen = $this->jenis_dokumen_model->get_all_publish_order_by('jenis_dokumen_name', 'desc');
        $ls_jenis_dokumen = $this->kelompok_model->get_all_publish_order_by('noid', 'asc');

        $data['ls_key'] = '';
        $data['ls_topik'] = $ls_topik;
        $data['ls_jenis_dokumen'] = $ls_jenis_dokumen;
        $data['ls_tanggal_from'] = '';
        $data['ls_tanggal_to'] = '';
        $data['ls_tahun'] = $this->regulasi_pajak_model->get_all_year();;
        $data['ls_nomor_from'] = '';
        $data['ls_nomor_to'] = '';
        /*--------------*/

        if(empty($this->uri->segment(3))) $page = 1;
        else $page = $this->uri->segment(3);

		$data['count_all'] = $this->regulasi_pajak_model->count_all_publish();
		$data['result'] = $this->regulasi_pajak_model->get_all_publish_perpage($page, $this->config->item('perpage'));
		$data['latest_per'] = $this->regulasi_pajak_model->get_latest_per();

		/*Pagination*/
        $config['base_url'] = site_url('peraturan-pajak/index');
		$config['total_rows'] =  $this->regulasi_pajak_model->count_all_publish();
		$config['per_page'] = $this->config->item('perpage');

		$config['use_page_numbers'] = true;
		$config['num_links'] = 3;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['next_link'] = 'Next';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = 'Prev';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';		

		$config['first_link'] = false;
		$config['last_link'] = false;

		$this->pagination->initialize($config);

		$data['paging'] = $this->pagination->create_links();
		/*----------*/

		$this->template->set('container_class', 'search-page');
		$this->template->set('title', 'Peraturan Pajak - '.$this->config->item('web_title'));
		$this->template->load('web/template/template-2', 'web/peraturanpajak/peraturanpajak', $data);
	}

	public function get_social()
	{
		$id = $this->input->post('id');

		$data = $this->regulasi_pajak_model->get($id);

		$url = site_url('peraturan-pajak/read/'.$data['permalink']);

		$html ='<a href="https://www.facebook.com/sharer/sharer.php?u='.$url.'" target="_blank" id="share-facebook"><span class="socicon socicon-facebook"></a>
				<a href="https://twitter.com/intent/tweet?url='.$url.'" target="_blank" id="share-twitter"><span class="socicon socicon-twitter"></a>
				<a href="https://www.linkedin.com/shareArticle?url='.$url.'" target="_blank" id="share-linkedin"><span class="socicon socicon-linkedin"></a>
				<a href="https://plus.google.com/share?url='.$url.'" target="_blank" id="share-googleplus"><span class="socicon socicon-googleplus"></a>
				<a href="http://line.me/R/msg/text/?'.$url.'" target="_blank" id="share-line"><span class="socicon socicon-line"></a>
				<a href="whatsapp://send?text='.$url.'" target="_blank" id="share-whatsapp"><span class="socicon socicon-whatsapp"></a>';

		echo $html;
	}

	public function read($url)
	{
		/*PP searchbox*/
        $ls_topik = $this->topik_model->get_all_publish_order_by('topik_id', 'asc');
        //$ls_jenis_dokumen = $this->jenis_dokumen_model->get_all_publish_order_by('jenis_dokumen_name', 'desc');
        $ls_jenis_dokumen = $this->kelompok_model->get_all_publish_order_by('noid', 'asc');

        $data['ls_key'] = '';
        $data['ls_topik'] = $ls_topik;
        $data['ls_jenis_dokumen'] = $ls_jenis_dokumen;
        $data['ls_tanggal_from'] = '';
        $data['ls_tanggal_to'] = '';
        $data['ls_tahun'] = $this->regulasi_pajak_model->get_all_year();;
        $data['ls_nomor_from'] = '';
        $data['ls_nomor_to'] = '';
        /*--------------*/

        $result = $this->regulasi_pajak_model->get_publish_by('permalink', $url);

        $data['result'] = $this->regulasi_pajak_model->get_publish_by('permalink', $url);

		$data['javascript'] = 	"<script>
							      $(document).ready(function(){
							        $('.modalcaller#".$result['id']."').trigger( 'click', [ '".$result['id']."' ]  );

							        return false;
							      });
							    </script>";

		$title = $result['jenis_dokumen_lengkap'].' Nomor: '.$result['nomordokumen'].' - Peraturan Pajak - '.$this->config->item('web_title');
		$description = substr(trim(preg_replace('/\s\s+/', ' ', strip_tags($result['body_final']))), 0, 255);

		$keywords = $result['jenis_dokumen_lengkap'].' '.$result['nomordokumen'];
		$keywords = str_replace(" ", ", ", strtolower($keywords));

		$data['meta'] =  '<meta name="description" content="'.$description.'">
						  <meta name="keywords" content="ddtc, taxengine, '.$keywords.'">
						  <meta name="author" content="TAX ENGINE">

						  <!-- facebook -->
						  <meta property="og:url"           content="'.current_url().'" />
						  <meta property="og:type"          content="website" />
						  <meta property="og:title"         content="'.$title.'" />
						  <meta property="og:description"   content="'.$description.'" />
						  <meta property="og:image"         content="'.site_url('assets/cover.jpg').'" />

						  <!-- twitter -->
						  <meta name="twitter:card" content="summary">
						  <meta name="twitter:url" content="'.current_url().'">
						  <meta name="twitter:title" content="'.$title.'">
						  <meta name="twitter:description" content="'.$description.'">
						  <meta name="twitter:image" content="'.site_url('assets/cover.jpg').'">';

		$this->template->set('container_class', 'search-page');
		$this->template->set('title', $title.' - Peraturan Pajak - '.$this->config->item('web_title'));
		$this->template->load('web/template/template-2', 'web/peraturanpajak/peraturanpajak-read', $data);
	}

	public function topsearch()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('key', 'Key word', 'trim|required');
		$this->form_validation->set_error_delimiters('<p class="help-block">', '</p>');

		if($this->form_validation->run() == FALSE)
		{
			$rurl = $this->input->post('rurl');

			redirect($rurl);
		}
		else
		{
			$key = $this->input->post('key');
			$key_url = url_title($key, '_', TRUE);

			$data_key_session = array('key_session' => $key);
			$this->session->set_userdata($data_key_session);

			redirect('peraturan-pajak/search_all/'.$key_url.'/semua-kategori/semua-dokumen/00-00-0000_00-00-0000/0000/0_0/kalimat/0/tahun');
		}
	}

	public function search()
	{
		$key = $this->uri->segment(3);
		$kategori = $this->uri->segment(4);
		$jenis_dokumen = $this->uri->segment(5);
		$tanggal = $this->uri->segment(6);
		$tahun = $this->uri->segment(7);
		$nomor = $this->uri->segment(8);
		$method = $this->uri->segment(9);
		$judul = $this->uri->segment(10);
		$sort = $this->uri->segment(11);

		$url_year = site_url().'peraturan-pajak/search/'.$key.'/'.$kategori.'/'.$jenis_dokumen.'/'.$tanggal.'/'.$tahun.'/'.$nomor.'/'.$method.'/'.$judul.'/tahun';
		$url_number = site_url().'peraturan-pajak/search/'.$key.'/'.$kategori.'/'.$jenis_dokumen.'/'.$tanggal.'/'.$tahun.'/'.$nomor.'/'.$method.'/'.$judul.'/nomor';

		$data['url_year'] = $url_year;
		$data['url_number'] = $url_number;

		$key_array = explode('_', $key);

		//$kategori_data = $this->topik_model->get_topik_by_url($kategori);

		$jenis_dokumen_array = explode('_', $jenis_dokumen);

		if(empty($this->uri->segment(12))) $page = 1;
        else $page = $this->uri->segment(12);

		if($kategori == 'semua-kategori')
		{
			if($jenis_dokumen_array[0] == 'semua-dokumen')
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
			else
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				//$jenis_dokumen_name_array = $this->jenis_dokumen_model->get_jenis_dokumen_name_array($jenis_dokumen_array);
				$jenis_dokumen_name_array = $this->kelompok_model->get_kelompok_array($jenis_dokumen_array);
				
				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
		}
		else
		{
			if(strpos($kategori, '_') !== false) {
				$kategori = explode('_', $kategori);
			}
			if($jenis_dokumen_array[0] == 'semua-dokumen')
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
			else
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				//$jenis_dokumen_name_array = $this->jenis_dokumen_model->get_jenis_dokumen_name_array($jenis_dokumen_array);
				$jenis_dokumen_name_array = $this->kelompok_model->get_kelompok_array($jenis_dokumen_array);
				
				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
		}

		$data['key'] = str_replace("_", " ", $key);

		$data['count'] = count($all);
		$data['count_all'] = $this->regulasi_pajak_model->count_all_publish();

		/*PP searchbox*/
        $ls_key = $this->uri->segment(3);
        $ls_key = str_replace("_", " ", $ls_key);

        $ls_topik = $this->topik_model->get_all_publish_order_by('topik_id', 'asc');

        //$ls_jenis_dokumen = $this->jenis_dokumen_model->get_all_publish_order_by('jenis_dokumen_name', 'desc');
        $ls_jenis_dokumen = $this->kelompok_model->get_all_publish_order_by('noid', 'asc');

        $ls_tanggal = $this->uri->segment(6);
        if($ls_tanggal) 
        {
            list($ls_tanggal_from,$ls_tanggal_to) = explode("_", $ls_tanggal);
        }
        else
        {
            $ls_tanggal_from = '';
            $ls_tanggal_to = '';
        }
        if($ls_tanggal_from == '00-00-0000') $ls_tanggal_from = '';
        if($ls_tanggal_to == '00-00-0000') $ls_tanggal_to = '';

        $ls_tahun = $this->regulasi_pajak_model->get_all_year();

        $ls_nomor = $this->uri->segment(8);
        if($ls_nomor) 
        {
            list($ls_nomor_from,$ls_nomor_to) = explode("_", $ls_nomor);
        }
        else
        {
            $ls_nomor_from = '';
            $ls_nomor_to = '';
        }
        if($ls_nomor_from == '0') $ls_nomor_from = '';
        if($ls_nomor_to == '0') $ls_nomor_to = '';

        $this->template->set('ls_key', $ls_key);
        $this->template->set('ls_topik', $ls_topik);
        $this->template->set('ls_jenis_dokumen', $ls_jenis_dokumen);
        $this->template->set('ls_tanggal_from', $ls_tanggal_from);
        $this->template->set('ls_tanggal_to', $ls_tanggal_to);
        $this->template->set('ls_tahun', $ls_tahun);
        $this->template->set('ls_nomor_from', $ls_nomor_from);
        $this->template->set('ls_nomor_to', $ls_nomor_to);

        $data['ls_key'] = $ls_key;
        $data['ls_topik'] = $ls_topik;
        $data['ls_jenis_dokumen'] = $ls_jenis_dokumen;
        $data['ls_tanggal_from'] = $ls_tanggal_from;
        $data['ls_tanggal_to'] = $ls_tanggal_to;
        $data['ls_tahun'] = $ls_tahun;
        $data['ls_nomor_from'] = $ls_nomor_from;
        $data['ls_nomor_to'] = $ls_nomor_to;
        /*--------------*/

        /*Pagination*/
        $config['base_url'] = site_url('peraturan-pajak/search/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.$this->uri->segment(5).'/'.$this->uri->segment(6).'/'.$this->uri->segment(7).'/'.$this->uri->segment(8).'/'.$this->uri->segment(9).'/'.$this->uri->segment(10).'/'.$this->uri->segment(11));
		$config['total_rows'] =  count($all);
		$config['per_page'] = $this->config->item('perpage');

		$config['use_page_numbers'] = true;
		$config['num_links'] = 3;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['next_link'] = 'Next';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = 'Prev';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';		

		$config['first_link'] = false;
		$config['last_link'] = false;

		$this->pagination->initialize($config);

		$data['paging'] = $this->pagination->create_links();
		/*----------*/

		/*Terkait*/
		$terms = str_replace("_", " ", $key);
		$data['p3b_article'] = $this->p3b_article_model->get_terkait($terms);
		$data['putusan_pengadilan'] = $this->putusan_pengadilan_model->get_terkait($terms);
		$data['mahkamahagung'] = $this->putusan_ma_model->get_terkait($terms);

		$data['count_terkait'] = count($data['p3b_article']) + count($data['putusan_pengadilan']) + count($data['mahkamahagung']);
		/*-------*/

		$data['latest_per'] = $this->regulasi_pajak_model->get_latest_per();

		$this->template->set('container_class', 'search-page');
		$this->template->set('title', 'Hasil Pencarian "'.str_replace("_", " ", $key).'" - Peraturan Pajak - '.$this->config->item('web_title'));
		$this->template->load('web/template/template-2', 'web/peraturanpajak/peraturanpajak-search', $data);
	}

	public function search_all()
	{
		$key = $this->uri->segment(3);
		$kategori = $this->uri->segment(4);
		$jenis_dokumen = $this->uri->segment(5);
		$tanggal = $this->uri->segment(6);
		$tahun = $this->uri->segment(7);
		$nomor = $this->uri->segment(8);
		$method = $this->uri->segment(9);
		$judul = $this->uri->segment(10);
		$sort = $this->uri->segment(11);

		$url_year = site_url().'peraturan-pajak/search/'.$key.'/'.$kategori.'/'.$jenis_dokumen.'/'.$tanggal.'/'.$tahun.'/'.$nomor.'/'.$method.'/'.$judul.'/tahun';
		$url_number = site_url().'peraturan-pajak/search/'.$key.'/'.$kategori.'/'.$jenis_dokumen.'/'.$tanggal.'/'.$tahun.'/'.$nomor.'/'.$method.'/'.$judul.'/nomor';

		$data['url_year'] = $url_year;
		$data['url_number'] = $url_number;

		$key_array = explode('_', $key);

		//$kategori_data = $this->topik_model->get_topik_by_url($kategori);

		$jenis_dokumen_array = explode('_', $jenis_dokumen);

		if(empty($this->uri->segment(12))) $page = 1;
        else $page = $this->uri->segment(12);

		if($kategori == 'semua-kategori')
		{
			if($jenis_dokumen_array[0] == 'semua-dokumen')
			{	
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
			else
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				//$jenis_dokumen_name_array = $this->jenis_dokumen_model->get_jenis_dokumen_name_array($jenis_dokumen_array);
				$jenis_dokumen_name_array = $this->kelompok_model->get_kelompok_array($jenis_dokumen_array);
				
				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
		}
		else
		{
			if(strpos($kategori, '_') !== false) {
				$kategori = explode('_', $kategori);
			}
			if($jenis_dokumen_array[0] == 'semua-dokumen')
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
			else
			{
				$terms = $this->session->userdata('key_session');
				//$terms = str_replace("_", " ", $key);

				//$jenis_dokumen_name_array = $this->jenis_dokumen_model->get_jenis_dokumen_name_array($jenis_dokumen_array);
				$jenis_dokumen_name_array = $this->kelompok_model->get_kelompok_array($jenis_dokumen_array);
				
				$data['result'] = $this->regulasi_pajak_model->get_search_result_perpage($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort, $page, $this->config->item('perpage'));

				$all = $this->regulasi_pajak_model->get_search_result_all($terms, $kategori, $jenis_dokumen_name_array, $tanggal, $tahun, $nomor, $method, $judul, $sort);
			}
		}

		$data['key'] = str_replace("_", " ", $key);

		$data['count'] = count($all);
		$data['count_all'] = $this->regulasi_pajak_model->count_all_publish();

		/*PP searchbox*/
        $ls_key = $this->uri->segment(3);
        $ls_key = str_replace("_", " ", $ls_key);

        $ls_topik = $this->topik_model->get_all_publish_order_by('topik_id', 'asc');

        //$ls_jenis_dokumen = $this->jenis_dokumen_model->get_all_publish_order_by('jenis_dokumen_name', 'desc');
        $ls_jenis_dokumen = $this->kelompok_model->get_all_publish_order_by('noid', 'asc');

        $ls_tanggal = $this->uri->segment(6);
        if($ls_tanggal) 
        {
            list($ls_tanggal_from,$ls_tanggal_to) = explode("_", $ls_tanggal);
        }
        else
        {
            $ls_tanggal_from = '';
            $ls_tanggal_to = '';
        }
        if($ls_tanggal_from == '00-00-0000') $ls_tanggal_from = '';
        if($ls_tanggal_to == '00-00-0000') $ls_tanggal_to = '';

        $ls_tahun = $this->regulasi_pajak_model->get_all_year();

        $ls_nomor = $this->uri->segment(8);
        if($ls_nomor) 
        {
            list($ls_nomor_from,$ls_nomor_to) = explode("_", $ls_nomor);
        }
        else
        {
            $ls_nomor_from = '';
            $ls_nomor_to = '';
        }
        if($ls_nomor_from == '0') $ls_nomor_from = '';
        if($ls_nomor_to == '0') $ls_nomor_to = '';

        $this->template->set('ls_key', $ls_key);
        $this->template->set('ls_topik', $ls_topik);
        $this->template->set('ls_jenis_dokumen', $ls_jenis_dokumen);
        $this->template->set('ls_tanggal_from', $ls_tanggal_from);
        $this->template->set('ls_tanggal_to', $ls_tanggal_to);
        $this->template->set('ls_tahun', $ls_tahun);
        $this->template->set('ls_nomor_from', $ls_nomor_from);
        $this->template->set('ls_nomor_to', $ls_nomor_to);

        $data['ls_key'] = $ls_key;
        $data['ls_topik'] = $ls_topik;
        $data['ls_jenis_dokumen'] = $ls_jenis_dokumen;
        $data['ls_tanggal_from'] = $ls_tanggal_from;
        $data['ls_tanggal_to'] = $ls_tanggal_to;
        $data['ls_tahun'] = $ls_tahun;
        $data['ls_nomor_from'] = $ls_nomor_from;
        $data['ls_nomor_to'] = $ls_nomor_to;
        /*--------------*/

        /*Pagination*/
        $config['base_url'] = site_url('peraturan-pajak/search_all/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.$this->uri->segment(5).'/'.$this->uri->segment(6).'/'.$this->uri->segment(7).'/'.$this->uri->segment(8).'/'.$this->uri->segment(9).'/'.$this->uri->segment(10).'/'.$this->uri->segment(11));
		$config['total_rows'] =  count($all);
		$config['per_page'] = $this->config->item('perpage');

		$config['use_page_numbers'] = true;
		$config['num_links'] = 3;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['next_link'] = 'Next';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = 'Prev';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';		

		$config['first_link'] = false;
		$config['last_link'] = false;

		$this->pagination->initialize($config);

		$data['paging'] = $this->pagination->create_links();
		/*----------*/

		/*Terkait*/
		$terms = str_replace("_", " ", $key);
		$data['p3b_article'] = $this->p3b_article_model->get_terkait($terms);
		$data['putusan_pengadilan'] = $this->putusan_pengadilan_model->get_terkait($terms);
		$data['mahkamahagung'] = $this->putusan_ma_model->get_terkait($terms);

		$data['count_terkait'] = count($data['p3b_article']) + count($data['putusan_pengadilan']) + count($data['mahkamahagung']);
		/*-------*/
		
		$data['latest_per'] = $this->regulasi_pajak_model->get_latest_per();

		$this->template->set('container_class', 'search-page');
		$this->template->set('title', 'Hasil Pencarian "'.str_replace("_", " ", $key).'" - Peraturan Pajak - '.$this->config->item('web_title'));
		$this->template->load('web/template/template-2', 'web/peraturanpajak/peraturanpajak-search-all', $data);
	}

	public function do_search()
	{
		if(!$this->config->item('search_peraturan_pajak_login'))
		{
			$this->load->library('form_validation');

			$this->form_validation->set_rules('search_key', 'Kata Kunci', 'trim');
			$this->form_validation->set_rules('search_category', 'Kategori', 'trim');
			$this->form_validation->set_rules('search_document[]', 'Jenis Dokumen', 'trim');
			$this->form_validation->set_rules('search_tanggal', 'Tanggal / Tahun', 'trim');
			$this->form_validation->set_rules('search_date_from', 'Tanggal Mulai', 'trim');
			$this->form_validation->set_rules('search_date_to', 'Tanggal Akhir', 'trim');
			$this->form_validation->set_rules('search_year', 'Tahun', 'trim');
			$this->form_validation->set_rules('search_number_from', 'Nomor Awal', 'trim');
			$this->form_validation->set_rules('search_number_to', 'Nomor Akhir', 'trim');
			$this->form_validation->set_rules('search_method', 'Metode Pencarian Kata', 'trim');
			$this->form_validation->set_rules('search_judul', 'Cari Hanya di Judul', 'trim');

			$this->form_validation->set_error_delimiters('<p class="help-block message-error">', '</p>');

			if($this->form_validation->run() == FALSE)
			{
				echo json_encode(
							array(
								'st' => 0, 'msg' => validation_errors()
								)
							);
			}
			else
			{
				$search_key 		= $this->input->post('search_key');
				$search_category 	= $this->input->post('search_category');
				$search_category_array = $this->input->post('search_category_array');
				if(!empty($search_category_array))
				{
					$search_category = implode('_', $search_category_array);
				}

				$search_document 	= $this->input->post('search_document');
				if(!empty($search_document))
				{
					$search_document = implode('_', $search_document);
				}
				else
				{
					$search_document = 'semua-dokumen';
				}

				$search_tanggal 	= $this->input->post('search_tanggal');
				$search_date_from 	= $this->input->post('search_date_from');
				$search_date_to 	= $this->input->post('search_date_to');
				$search_year 		= $this->input->post('search_year');
				$search_number_from = $this->input->post('search_number_from');
				$search_number_to 	= $this->input->post('search_number_to');
				$search_method 		= $this->input->post('search_method');
				$search_judul 		= $this->input->post('search_judul');

				if(!$search_key) $search_key = 'semua';
				if(!$search_date_from) $search_date_from = '00-00-0000';
				if(!$search_date_to) $search_date_to = '00-00-0000';
				if(!$search_year) $search_year = '0000';	
				if(!$search_number_from) $search_number_from = '0';
				if(!$search_number_to) $search_number_to = '0';
				if(!$search_judul) $search_judul = '0';
				if(!$search_category) $search_category = 'semua-kategori';

				$key_url = url_title($search_key, '_', TRUE);

				$data_key_session = array('key_session' => $search_key);
				$this->session->set_userdata($data_key_session);

				echo json_encode(
							array(
									'st' => 1, 
									'msg' => '<p class="help-block message-success-alt-1">Tunggu...</p>',
									'url' => site_url().'peraturan-pajak/search/'.$key_url.'/'.$search_category.'/'.$search_document.'/'.$search_date_from.'_'.$search_date_to.'/'.$search_year.'/'.$search_number_from.'_'.$search_number_to.'/'.$search_method.'/'.$search_judul.'/tahun'
								)
							);
			}
		}
		else
		{
			if($this->user_auth->is_logged_in())
			{
				$this->load->library('form_validation');

				$this->form_validation->set_rules('search_key', 'Kata Kunci', 'trim');
				$this->form_validation->set_rules('search_category', 'Kategori', 'trim');
				$this->form_validation->set_rules('search_document[]', 'Jenis Dokumen', 'trim');
				$this->form_validation->set_rules('search_tanggal', 'Tanggal / Tahun', 'trim');
				$this->form_validation->set_rules('search_date_from', 'Tanggal Mulai', 'trim');
				$this->form_validation->set_rules('search_date_to', 'Tanggal Akhir', 'trim');
				$this->form_validation->set_rules('search_year', 'Tahun', 'trim');
				$this->form_validation->set_rules('search_number_from', 'Nomor Awal', 'trim');
				$this->form_validation->set_rules('search_number_to', 'Nomor Akhir', 'trim');
				$this->form_validation->set_rules('search_method', 'Metode Pencarian Kata', 'trim');
				$this->form_validation->set_rules('search_judul', 'Cari Hanya di Judul', 'trim');

				$this->form_validation->set_error_delimiters('<p class="help-block message-error">', '</p>');

				if($this->form_validation->run() == FALSE)
				{
					echo json_encode(
								array(
									'st' => 0, 'msg' => validation_errors()
									)
								);
				}
				else
				{
					$search_key 		= $this->input->post('search_key');
					$search_category 	= $this->input->post('search_category');
					$search_category_array = $this->input->post('search_category_array');
					if(!empty($search_category_array))
					{
						$search_category = implode('_', $search_category_array);
					}

					$search_document 	= $this->input->post('search_document');
					if(!empty($search_document))
					{
						$search_document = implode('_', $search_document);
					}
					else
					{
						$search_document = 'semua-dokumen';
					}

					$search_tanggal 	= $this->input->post('search_tanggal');
					$search_date_from 	= $this->input->post('search_date_from');
					$search_date_to 	= $this->input->post('search_date_to');
					$search_year 		= $this->input->post('search_year');
					$search_number_from = $this->input->post('search_number_from');
					$search_number_to 	= $this->input->post('search_number_to');
					$search_method 		= $this->input->post('search_method');
					$search_judul 		= $this->input->post('search_judul');

					if(!$search_key) $search_key = 'semua';
					if(!$search_date_from) $search_date_from = '00-00-0000';
					if(!$search_date_to) $search_date_to = '00-00-0000';
					if(!$search_year) $search_year = '0000';
					if(!$search_number_from) $search_number_from = '0';
					if(!$search_number_to) $search_number_to = '0';
					if(!$search_judul) $search_judul = '0';
					if(!$search_category) $search_category = 'semua-kategori';

					$key_url = url_title($search_key, '_', TRUE);

					$data_key_session = array('key_session' => $search_key);
					$this->session->set_userdata($data_key_session);

					echo json_encode(
								array(
										'st' => 1, 
										'msg' => '<p class="help-block message-success-alt-1">Tunggu...</p>',
										'url' => site_url().'peraturan-pajak/search/'.$key_url.'/'.$search_category.'/'.$search_document.'/'.$search_date_from.'_'.$search_date_to.'/'.$search_year.'/'.$search_number_from.'_'.$search_number_to.'/'.$search_method.'/'.$search_judul.'/tahun'
									)
								);
				}
			}
			else
			{
				echo json_encode(
								array(
										'st' => 2
									)
								);
			}
		}
	}

	public function download()
	{
		redirect('home');
		/*
		if($this->user_auth->is_logged_in())
		{
			$type = $this->uri->segment(3);
			$id = $this->uri->segment(4);

			if($type == 'lampiran')
			{
				$regulasi_pajak = $this->regulasi_pajak_model->get($id);
				$lamp1_file = $regulasi_pajak['lamp1_file'];
				$id_dj = $regulasi_pajak['id_dj'];

				$filename = 'Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'.pdf';

				if(!$lamp1_file || $lamp1_file == NULL)
				{
					if(!$id_dj || $id_dj == NULL)
					{
						redirect('home');
					}
					else
					{
						$path = file_get_contents(base_url().'/assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf');	
					}
				}
				else
				{
					$path = file_get_contents(base_url().'/assets/download/peraturanpajak/file/'.$lamp1_file);
				}

				//echo '<pre>';
				//print_r($regulasi_pajak);
				//echo '</pre>';

				force_download($filename, $path);
				exit();
			}
		}
		else
		{
			redirect('home');
		}
		*/
	}

	public function get_lampiran_old()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$id_dj = $regulasi_pajak['id_dj'];

		if(!$id_dj || $id_dj == NULL)
		{
			$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';

			echo json_encode(
							array(
									'st' => 0,
									'url' => $url_lampiran
									)
							);
		}
		else
		{
			$path = file_get_contents(base_url().'/assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf');

			$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'peraturan-pajak/download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';

			echo json_encode(
							array(
									'st' => 1,
									'url' => $url_lampiran
									)
							);
		}
	}

	public function get_lampiran()
	{
		if($this->user_auth->is_logged_in()) {
			$id = $this->input->post('id');

			$regulasi_pajak = $this->regulasi_pajak_model->get($id);
			$lamp1_file = $regulasi_pajak['lamp1_file'];
			$id_dj = $regulasi_pajak['id_dj'];

			if(!$lamp1_file || $lamp1_file == NULL)
			{
				if(!$id_dj || $id_dj == NULL)
				{
					$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

					echo json_encode(
									array(
											'st' => 0,
											'url' => $url_lampiran
											)
									);
				}
				else
				{
					$path = file_get_contents(base_url().'/assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf');

					$filename = './assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf';

					if(file_exists($filename)) 
					{
						//$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'peraturan-pajak/download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';
						//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';
						$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';

						echo json_encode(
										array(
												'st' => 1,
												'url' => $url_lampiran
												)
										);
					} 
					else 
					{
						$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
						//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

						echo json_encode(
										array(
												'st' => 0,
												'url' => $url_lampiran
												)
										);
					}
				}
			}
			else
			{
				$path = file_get_contents(base_url().'/assets/download/peraturanpajak/file/'.$lamp1_file);

				$filename = './assets/download/peraturanpajak/file/'.$lamp1_file;

				if(file_exists($filename)) 
				{
					$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';
					
					echo json_encode(
									array(
											'st' => 1,
											'url' => $url_lampiran
											)
									);
				}
				else
				{
					$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

					echo json_encode(
									array(
											'st' => 0,
											'url' => $url_lampiran
											)
									);
				}
			}
		} else {
			// echo '0';
			$id = $this->input->post('id');

			$regulasi_pajak = $this->regulasi_pajak_model->get($id);
			$lamp1_file = $regulasi_pajak['lamp1_file'];
			$id_dj = $regulasi_pajak['id_dj'];

			if(!$lamp1_file || $lamp1_file == NULL)
			{
				if(!$id_dj || $id_dj == NULL)
				{
					$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

					echo json_encode(
									array(
											'st' => 0,
											'url' => $url_lampiran
											)
									);
				}
				else
				{
					$path = file_get_contents(base_url().'/assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf');

					$filename = './assets/download/peraturanpajak/lampiran/'.$id_dj.'.pdf';

					if(file_exists($filename)) 
					{
						$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';
						//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

						echo json_encode(
										array(
												'st' => 1,
												'url' => $url_lampiran
												)
										);
					} 
					else 
					{
						$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
						//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

						echo json_encode(
										array(
												'st' => 0,
												'url' => $url_lampiran
												)
										);
					}
				}
			}
			else
			{
				$path = file_get_contents(base_url().'/assets/download/peraturanpajak/file/'.$lamp1_file);

				$filename = './assets/download/peraturanpajak/file/'.$lamp1_file;

				if(file_exists($filename)) 
				{
					$url_lampiran = '<ul class="tools-list-items"><li><a href="'.site_url().'download/lampiran/'.$id.'" target="_blank">Lampiran '.$regulasi_pajak['jenis_dokumen_lengkap'].' Nomor '.$regulasi_pajak['nomordokumen'].'</a></li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';
					
					echo json_encode(
									array(
											'st' => 1,
											'url' => $url_lampiran
											)
									);
				}
				else
				{
					$url_lampiran = '<ul class="tools-list-items"><li>Tidak ada lampiran</li></ul>';
					//$url_lampiran = '<ul class="tools-list-items"><li>Segera hadir.</li></ul>';

					echo json_encode(
									array(
											'st' => 0,
											'url' => $url_lampiran
											)
									);
				}
			}
		}
	}

	public function get_terkait_old()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$id_o = $regulasi_pajak['id_o'];

		$linklist = get_linklist($id_o);

		if($linklist != '')
		{
			$terkait = print_linklist($linklist);
		}
		else
		{
			$terkait = 0;
		}

		echo $terkait;
	}

	public function get_terkait_test($id)
	{
		//$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$linklist_rp  = $regulasi_pajak['linklist'];
		$id_o = $regulasi_pajak['id_o'];

		if(!$id_o || $id_o == NULL || $id_o == 0)
		{
			$linklist = $linklist_rp;

			if($linklist != '')
			{
				$terkait = print_linklist_rp($linklist);
			}
			else
			{
				$terkait = 0;
			}
		}
		else
		{
			if(!$linklist_rp || $linklist_rp == NULL || $linklist_rp == 0)
			{
				$linklist = get_linklist($id_o);

				if($linklist != '')
				{
					$terkait = print_linklist($linklist);
				}
				else
				{
					$terkait = 0;
				}
			}
			else
			{
				$linklist = $linklist_rp;

				if($linklist != '')
				{
					$terkait = print_linklist_rp($linklist);
				}
				else
				{
					$terkait = 0;
				}	
			}
		}

		echo $terkait;
	}

	public function get_terkait()
	{
		if($this->user_auth->is_logged_in()) {
			$id = $this->input->post('id');

			$regulasi_pajak = $this->regulasi_pajak_model->get($id);
			$linklist_rp  = $regulasi_pajak['linklist'];
			$id_o = $regulasi_pajak['id_o'];

			if(!$id_o || $id_o == NULL || $id_o == 0)
			{
				$linklist = $linklist_rp;

				if($linklist != '')
				{
					$terkait = print_linklist_rp($linklist);
				}
				else
				{
					$terkait = 0;
				}
			}
			else
			{
				if(!$linklist_rp || $linklist_rp == NULL || $linklist_rp == 0)
				{
					$linklist = get_linklist($id_o);

					if($linklist != '')
					{
						$terkait = print_linklist($linklist);
					}
					else
					{
						$terkait = 0;
					}
				}
				else
				{
					$linklist = $linklist_rp;

					if($linklist != '')
					{
						$terkait = print_linklist_rp($linklist);
					}
					else
					{
						$terkait = 0;
					}	
				}
			}

			echo $terkait;
		} else {
			// echo '0';
			$id = $this->input->post('id');

			$regulasi_pajak = $this->regulasi_pajak_model->get($id);
			$linklist_rp  = $regulasi_pajak['linklist'];
			$id_o = $regulasi_pajak['id_o'];

			if(!$id_o || $id_o == NULL || $id_o == 0)
			{
				$linklist = $linklist_rp;

				if($linklist != '')
				{
					$terkait = print_linklist_rp($linklist);
				}
				else
				{
					$terkait = 0;
				}
			}
			else
			{
				if(!$linklist_rp || $linklist_rp == NULL || $linklist_rp == 0)
				{
					$linklist = get_linklist($id_o);

					if($linklist != '')
					{
						$terkait = print_linklist($linklist);
					}
					else
					{
						$terkait = 0;
					}
				}
				else
				{
					$linklist = $linklist_rp;

					if($linklist != '')
					{
						$terkait = print_linklist_rp($linklist);
					}
					else
					{
						$terkait = 0;
					}	
				}
			}

			echo $terkait;
		}
	}

	public function get_riwayat()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$historylist_rp  = $regulasi_pajak['historylist'];
		$id_o = $regulasi_pajak['id_o'];

		if(!$id_o || $id_o == NULL || $id_o == 0)
		{
			$history = $historylist_rp;

			if($history != '')
			{
				$riwayat = print_history_rp($history);
			}
			else
			{
				$riwayat = 0;
			}
		}
		else
		{
			if(!$historylist_rp || $historylist_rp == NULL || $historylist_rp == 0)
			{
				$history = get_history($id_o);

				if($history != '')
				{
					$riwayat = print_history($history);
				}
				else
				{
					$riwayat = 0;
				}
			}
			else
			{
				$history = $historylist_rp;

				if($history != '')
				{
					$riwayat = print_history_rp($history);
				}
				else
				{
					$riwayat = 0;
				}
			}
		}

		echo $riwayat;
	}

	public function get_riwayat_old()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$id_o = $regulasi_pajak['id_o'];

		$history = get_history($id_o);

		if($history != '')
		{
			$riwayat = print_history($history);
		}
		else
		{
			$riwayat = 0;
		}

		echo $riwayat;
	}

	public function get_status()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$statuslist_rp  = $regulasi_pajak['statuslist'];
		$id_o = $regulasi_pajak['id_o'];

		if(!$id_o || $id_o == NULL || $id_o == 0)
		{
			$statuslist = $statuslist_rp;

			if($statuslist != '')
			{
				$status = print_status_rp($statuslist);
			}
			else
			{
				$status = 0;
			}
		}
		else
		{
			if(!$statuslist_rp || $statuslist_rp == NULL || $statuslist_rp == 0)
			{
				$statuslist = get_status($id_o);

				if($statuslist != '')
				{
					$status = print_status($statuslist);
				}
				else
				{
					$status = 0;
				}
			}
			else
			{
				$statuslist = $statuslist_rp;

				if($statuslist != '')
				{
					$status = print_status_rp($statuslist);
				}
				else
				{
					$status = 0;
				}
			}
		}

		echo $status;
	}

	public function get_status_old()
	{
		$id = $this->input->post('id');

		$regulasi_pajak = $this->regulasi_pajak_model->get($id);
		$id_o = $regulasi_pajak['id_o'];

		$statuslist = get_status($id_o);

		if($statuslist != '')
		{
			$status = print_status($statuslist);
		}
		else
		{
			$status = 0;
		}

		echo $status;
	}

	public function get_body_final_()
	{
		if($this->user_auth->is_logged_in())
		{
			$id = $this->input->post('id');
			$regulasi_pajak = $this->regulasi_pajak_model->get($id);

			echo $regulasi_pajak['body_final'].'<div class="footerdoc"><img src="'.site_url().'assets/themes/images/newdocfooter.png"></div>';
		}
		else
		{
			echo '0';
		}
	}

	public function get_body_final()
	{
		// if($this->user_auth->is_logged_in())
		if($this->config->item('peraturan_pajak_login') && !$this->user_auth->is_logged_in())
		{
			$id = $this->input->post('id');
			$pj = $this->regulasi_pajak_model->get($id);
			$pj_view = $pj['view'];

			$pj_view_new = (int)$pj_view+1;

			$data = array('view' => $pj_view_new);
			$this->regulasi_pajak_model->update($id, $data);
			
			$regulasi_pajak = $this->regulasi_pajak_model->get($id);

			$id_o = $regulasi_pajak['id_o'];
			$body_final = $regulasi_pajak['body_final'];

			if(!$id_o || $id_o == NULL || $id_o == 0)
			{
				$linklist  = $regulasi_pajak['linklist'];

				if($linklist != '') 
				{
	                $body_replace = regulasi_ortax_format_body_rp($linklist, $body_final);
	            } 
	            else
	            {
	                $body_replace = $body_final;
	            }
			}
			else
			{
				$linklist = get_linklist($id_o);

				if($linklist != '') 
				{
	                $body_replace = regulasi_ortax_format_body($linklist, $body_final);
	            } 
	            else
	            {
	                $body_replace = $body_final;
	            }
			}

			echo '<div class="nologin-readmore"><a href="#" id="readmore-login">READ MORE</a></div>'.$body_replace.'<div class="footerdoc"><img src="'.site_url().'assets/themes/images/newdocfooter.png"></div>';
		}
		else
		{
			// echo '0';

			$id = $this->input->post('id');
			$pj = $this->regulasi_pajak_model->get($id);
			$pj_view = $pj['view'];

			$pj_view_new = (int)$pj_view+1;

			$data = array('view' => $pj_view_new);
			$this->regulasi_pajak_model->update($id, $data);
			
			$regulasi_pajak = $this->regulasi_pajak_model->get($id);

			$id_o = $regulasi_pajak['id_o'];
			$body_final = $regulasi_pajak['body_final'];

			if(!$id_o || $id_o == NULL || $id_o == 0)
			{
				$linklist  = $regulasi_pajak['linklist'];

				if($linklist != '') 
				{
	                $body_replace = regulasi_ortax_format_body_rp($linklist, $body_final);
	            } 
	            else
	            {
	                $body_replace = $body_final;
	            }
			}
			else
			{
				$linklist = get_linklist($id_o);

				if($linklist != '') 
				{
	                $body_replace = regulasi_ortax_format_body($linklist, $body_final);
	            } 
	            else
	            {
	                $body_replace = $body_final;
	            }
			}

			echo $body_replace.'<div class="footerdoc"><img src="'.site_url().'assets/themes/images/newdocfooter.png"></div>';
		}
	}

	public function get_body_final_old()
	{
		if($this->user_auth->is_logged_in())
		{
			$id = $this->input->post('id');
			$pj = $this->regulasi_pajak_model->get($id);
			$pj_view = $pj['view'];

			$pj_view_new = (int)$pj_view+1;

			$data = array('view' => $pj_view_new);
			$this->regulasi_pajak_model->update($id, $data);
			
			$regulasi_pajak = $this->regulasi_pajak_model->get($id);

			$id_o = $regulasi_pajak['id_o'];
			$body_final = $regulasi_pajak['body_final'];

			if(!$id_o || $id_o == NULL || $id_o == 0)
			{
				$linklist  = $regulasi_pajak['linklist'];
			}
			else
			{
				$linklist = get_linklist($id_o);
			}

			if($linklist != '') 
			{
                $body_replace = regulasi_ortax_format_body($linklist, $body_final);
            } 
            else
            {
                $body_replace = $body_final;
            }

			echo $body_replace.'<div class="footerdoc"><img src="'.site_url().'assets/themes/images/newdocfooter.png"></div>';
		}
		else
		{
			echo '0';
		}
	}

	public function create_cookie_sanding()
	{
		$pj_id = $this->input->post('doc_1');

		$pj = $this->regulasi_pajak_model->get($pj_id);

		set_cookie('cookie_pj', 'yes', 3600);
		set_cookie('cookie_pj_id', $pj_id, 3600);
		set_cookie('cookie_pj_text', $pj['jenis_dokumen_lengkap'] .' Nomor: '. $pj['nomordokumen'], 3600);
	}

	public function delete_cookie_sanding()
	{
		delete_cookie('cookie_pj');
		delete_cookie('cookie_pj_id');
		delete_cookie('cookie_pj_text');
	}

	public function sanding()
	{
		$id = $this->input->post('id');
		$regulasi_pajak = $this->regulasi_pajak_model->get($id);

		echo $regulasi_pajak['jenis_dokumen_lengkap'] .' Nomor: '. $regulasi_pajak['nomordokumen'];
	}

	public function cetaks()
	{
        $tes = "<html><head><style>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700,600); table tr{page-break-inside:avoid;} body,p, table tr td { font-family: 'Open Sans'; font-size:10px !important;line-height:12px !important; } .nocompare-content-pp {height:auto !important;} table img {margin:0 auto;display:block;max-width:100%;height:auto;} html, body {height:auto !important;} .tablewrap {padding:0 !important;display:block !important;width:400px !important;} .tablewrap .tablewrap {padding:0 !important;margin:0 !important;} .nocompare-content {padding:0 !important;}</style>";
        //$tes .= "<link href='". base_url() ."assets/themes/css/custom.css?v=5' rel='stylesheet' type='text/css'>";
        $tes .= "</head><body>";
        $tes .= "<div class='doc-modal-pp'>";
        $tes .= "<div class='modal-desc' id='modal-contents-pp'>";
        $tes .= $this->input->post('doc');
        $tes .= "</div>";
        $tes .= "</div>";
        $tes .= "<script src='". base_url() ."assets/themes/js/jquery.min.js'></script>";
        $tes .= "<script src='". base_url() ."assets/themes/js/html2canvas.js'></script>";
        $tes .= "<script src='". base_url() ."assets/themes/js/converttable.js'></script>";
        $tes .= "</body></html>";
        try{
            $dir = 'newdir';
            if ( !file_exists($dir) ) {
                    $oldmask = umask(0);  // helpful when used in linux server  
                    mkdir ($dir, 0777);
            } else{                
                //chmod("newdir", 0777);
            }
            
            $rawfilename = mt_rand();
            $filename = 'newdir/'.mt_rand() . '.html';
            file_put_contents($filename, $tes);
            chmod($filename, 0777);
            $cmd = '/usr/bin/xvfb-run --server-args="-screen 0, 1024x768x24" /usr/local/bin/wkhtmltopdf --no-outline --margin-top 10 --margin-right 10 --margin-bottom 15 --margin-left 10 --disable-smart-shrinking '.$filename.' --encoding utf-8 --footer-html newdir/assets/footer.html newdir/'.$rawfilename.'.pdf 2>&1';
            exec($cmd, $var, $res);

            //echo (print_r(array_values($var)));
            echo $rawfilename;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        

	}

	public function cetaksonline()
	{
        $tes = "<html><head><style>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700,600); table tr, p, img, ul, ol{page-break-before: always !important;page-break-inside: avoid !important;} body,p, table tr td { font-family: 'Open Sans'; font-size:10px !important;line-height:12px !important; } .nocompare-content-pp {height:auto !important;} table img {margin:0 auto !important;display:block;padding:0 !important;max-width:100% !important;height:auto !important;} html, body {height:auto !important;margin:0 !important;padding:0 !important;} .tablewrap {padding:0 !important;width:100% !important;display:block !important;overflow:inherit !important;} .tablewrap .tablewrap {padding:0 !important;margin:0 !important;} .nocompare-content {overflow-x:inherit !important;overflow-y:hidden !important;padding:0 !important;} .doc-modal-pp table table .wi{padding:0 !important;} body, html{overflow:hidden !important;}</style>";
        $tes .= "<link href='". base_url() ."assets/themes/css/custom.css?v=5' rel='stylesheet' type='text/css'>";
        $tes .= "</head><body>";
        $tes .= "<div class='doc-modal-pp'>";
        $tes .= "<div class='modal-desc' id='modal-contents-pp' style='page-break-before: always;page-break-inside:avoid;'>";
        $tes .= "<img src='http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg' width='0' height='0' />";
        $tes .= "<div style='height: 335px;' class='nocompare-content nocompare-content-pp' id='nocompare-wrapper-pp'><p class='head headtop'><strong>Putusan Pengadilan Pajak Nomor : Put-59334/PP/M.XVB/16/2015</strong></p><p style='text-align:center'><strong>RISALAH</strong><br>Putusan Pengadilan Pajak Nomor : Put-59334/PP/M.XVB/16/2015</p><p style='text-align:center'><strong>JENIS PAJAK</strong><br>Pajak Pertambahan Nilai</p><p style='text-align:center'><strong>TAHUN PAJAK</strong><br>2008</p><p style='text-align:center'><strong>POKOK SENGKETA</strong><br>bahwa yang menjadi pokok sengketa adalah pengajuan banding terhadapkoreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00);</p><table align='left' border='0' cellpadding='0' cellspacing='0'>	<tbody>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Terbanding</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa PPN yang dibayar oleh Pemohon Banding atas pembayaran apa yang disebut Pemohon Banding sebagai royalti dan ERP karena tidak terbukti sebagai pembayaran yang mempunyai hubungan Iangsung dengan usaha;</p>			<p>bahwa sehubungan dengan uraian penelitian atas DPP Pemanfaatan BKP Tidak Berwujud di atas, maka dapat disimpulkan bahwa terdapat koreksi atas DPP Pemanfaatan BKP Tidak Berwujud dari Luar Daerah Pabean, oleh karena itu atas pembayaran PPN sejumlah Rp4.919.183.561,00 yang menjadi pokok sengketa pada penelitian ini adalah bukan pembayaran PPN atas pemanfaatan BKP Tidak Berwujud dari Luar Daerah Pabean sehingga tidak dapat dikreditkan sebagai Pajak Masukan;</p>			</div></td>		</tr>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Pemohon</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa Pemohon Banding <strong> </strong><strong>tidak</strong><strong> </strong><strong>setuju</strong><strong> </strong>dengan koreksi yang dilakukan Terbanding dan <strong> </strong><strong>mengajukan</strong><strong> </strong><strong>banding</strong><strong> </strong>atas koreksi PPN yang dapat diperhitungkan sehubungan dengan Pembayaran PPN atas Pemanfaatan JKP dan/atau BKP Tidak Berwujud dari luar daerah pabean berupa Intercompany Technical Assistance dan Enterprise Resource Planning (ERP) Platform sebesar Rp 4.919.183.561,00;</p>			<p>bahwa Pemohon Banding nyata-nyata telah melakukan pembayaran PPN dan melaporkan dalam SPT Masa PPN atas pemanfaatan JKP dan/atau BKP Tidak Berwujud dari luar daerah pabean;</p>			</div></td>		</tr>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Majelis</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00)karena merupakan konsistensi dari adanya koreksi pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) Fee pada sengketa koreksi Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa menurut pendapat Majelis, Terbanding menetapkan pembayaran objek sebesar Rp49.191.835.605,00 bukan merupakan pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP), melainkan pembayaran dividen kepada pemegang saham sehingga bukan merupakan objek PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean;</p>			<p>bahwa oleh karena koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean bersumber dari koreksi positif atas kedua biaya tersebut di atas, maka pertimbangan dan kesimpulan Majelis terhadap sengketa ini mengikuti hasil pemeriksaan Majelis terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) pada sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008 berupa koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) telah diputus oleh Pengadilan Pajak dengan Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015;</p>			<p>bahwa hasil pemeriksaan, pertimbangan dan kesimpulan Majelis terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP)sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015 adalah sebagai berikut :<br>			koreksi positif Intercompany Technical Assistance Fee sebesar USD5,349,708.00</p>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi positif <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00karena perhitungan biaya Intercompany Technical Assistance Fee Pemohon Banding berdasarkan kepada Operating Income dan Third Party Revenue, bukan atas dasar kekayaan intelektual tertentu yang digunakan oleh Pemohon Banding;bahwa menurut Terbanding, metode perhitungan Pemohon Banding tidak lazim karena dengan metode ini dapat terjadi tidak ada <em>Technical Assistance Fee </em>yang harus dikeluarkan padahal seharusnya apabila telah diketahui terjadi penggunaan kekayaan intelektual maka akan timbul biaya yang harus dibebankan oleh Pemohon Banding yang erat hubungannya dalam rangka biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana disebutkan dalam Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Terbanding ketidaklaziman biaya <em>Intercompany Technical Assistance Fee </em>yang dibayarkan kepada Halliburton Energy Services, Inc ini pada prinsipnya adalah merupakan pembagian laba (Dividen) sesuai dengan Pasal 4 ayat (1) huruf g Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Terbanding, biaya <em>Intercompany Technical Assistance Fee</em>sebesar USD5,349,708.00 dikoreksi positif oleh Terbanding sesuai Pasal 9 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding tidak mempunyai resources untuk melakukan Research &amp; Development, dan tidak pernah menemukan sendiri baik itu teknologi, formula, maupun metode yang digunakan dalam proses jasa di bidang minyak dan gas bumi;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding tidak mungkin dapat melakukan penyerahan jasa tanpa menggunakan teknologi, formula, maupun metode yang hak patennya dimiliki oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut Pemohon Banding, biaya <em>Technical Assistance Fee </em>merupakan biaya yang dapat dikurangkan sesuai dengan Pasal 6 ayat (1) huruf a Undang-Undang Pajak Penghasilan, sedangkan pembayaran atas Technical Assistance Fee telah dilakukan melalui intercompany settlement;</p>			<p>bahwa menurut pendapat Majelis, Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan pengurangan serta menentukan utang sebagai modal untuk menghitung besarnya Penghasilan Kena Pajak bagi Wajib Pajak yang mempunyai hubungan istimewa dengan Wajib Pajak lainnya sesuai dengan kewajaran dan kelaziman usaha yang tidak dipengaruhi oleh hubungan istimewa</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>maksud diadakannya ketentuan ini adalah untuk mencegah terjadinya penghindaran pajak, yang dapat terjadi karena adanya hubungan istimewa. Apabila terdapat hubungan istimewa, kemungkinan dapat terjadi penghasilan dilaporkan kurang dari semestinya ataupun pembebanan biaya melebihi dari yang seharusnya. Dalam hal demikian Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan atau biaya sesuai dengan keadaan seandainya di antara para Wajib Pajak tersebut tidak terdapat hubungan istimewa. Dalam menentukan kembali jumlah penghasilan dan atau biaya tersebut dapat dipakai beberapa pendekatan, misalnya data pembanding, alokasi laba berdasar fungsi atau peran serta dari Wajib Pajak yang mempunyai hubungan istimewa dan</em><em> </em><em>indikasi</em><em> </em><em>serta</em><em> </em><em>data</em><em> </em><em>lainnya.</em><em> </em><em>Demikian</em><em> </em><em>pula</em><em> </em><em>kemungkinan</em><em> </em><em>terdapat</em><em> </em><em>penyertaan</em><em> </em><em>modal</em><em> </em><em>secara terselubung, dengan menyatakan penyertaan modal tersebut sebagai utang, maka Direktur Jenderal Pajak berwenang untuk menentukan utang tersebut sebagai modal perusahaan. Penentuan tersebut dapat dilakukan misalnya melalui indikasi mengenai perbandingan antara modal dengan utang yang lazim terjadi antara para pihak yang tidak dipengaruhi oleh hubungan istimewa atau berdasar data atau indikasi lainnya. Dengan demikian bunga yang dibayarkan sehubungan dengan utang yang dianggap sebagai penyertaan modal itu tidak diperbolehkan untuk dikurangkan, sedangkan bagi pemegang saham yang menerima atau memperolehnya dianggap sebagai dividen yang dikenakan pajak.</em>”</p>			<p>bahwa Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 menyatakan :<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>sebagaimana</em><em> </em><em>dimaksud</em><em> </em><em>dalam</em><em> </em><em>ayat</em><em> </em><em>(3)</em><em> </em><em>dan</em><em> </em><em>(3a),</em><em> </em><em>Pasal</em><em> </em><em>8</em><em> </em><em>ayat</em><em> </em><em>(4),</em><em> </em><em>Pasal</em><em> </em><em>9</em><em> </em><em>ayat (1) huruf f, dan Pasal 10 ayat (1) dianggap ada apabila :<br>			a. <em> </em>Wajib Pajak mempunyai penyertaan modal langsung atau tidak langsung paling rendah 25% (dua puluh lima persen) pada Wajib Pajak lain, atau hubungan antara Wajib Pajak dengan penyertaan paling rendah 25% (dua puluh lima persen) pada dua Wajib Pajak atau lebih, demikian pula hubungan antara dua Wajib Pajak atau lebih yang disebut terakhir; atau<br>			b. Wajib<em> </em>Pajak<em> </em>menguasai</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>lainnya</em><em> </em><em>atau</em><em> </em><em>dua</em><em> </em><em>atau</em><em> </em><em>lebih</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>berada</em><em> </em><em>di</em><em> </em><em>bawah penguasaan yang sama baik langsung maupun tidak langsung; atau</em><br>			c. <em>terdapat hubungan keluarga baik sedarah maupun semenda dalam garis keturunan lurus dan atau ke samping satu derajat;</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan:<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>di</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>ketergantungan</em><em> </em><em>atau</em><em> </em><em>keterikatan satu dengan yang lain yang disebabkan karena :<br>			a. <em> </em>kepemilikan atau penyertaan modal;b. </em><em> </em><em>adanya penguasaan melalui manajemen atau penggunaan teknologi.<br>			Selain karena hal-hal tersebut di atas, hubungan istimewa di antara Wajib Pajak orang pribadi dapat pula terjadi karena adanya hubungan darah atau karena perkawinan;</em></p>			<p><em>Huruf a<br>			Hubungan istimewa dianggap ada apabila terdapat hubungan kepemilikan yang berupa penyertaan modal sebesar 25% (dua puluh lima persen) atau lebih secara langsung ataupun tidak langsung. Misalnya, PT A mempunyai 50% (lima puluh persen) saham PT B. Pemilikan saham oleh PT A merupakan penyertaan langsung. Selanjutnya apabila PT B tersebut mempunyai 50% (lima puluh persen) saham PT C, maka PT A sebagai pemegang saham PT B secara tidak langsung mempunyai penyertaan pada PT C sebesar 25% (dua puluh lima persen). Dalam hal demikian antara PT A, PT B dan PT C dianggap terdapat hubungan istimewa. Apabila PT A juga memiliki 25% (dua puluh lima persen) saham PT D, maka antara PT B, PT C dan PT D dianggap terdapat hubungan istimewa. Hubungan kepemilikan seperti tersebut di atas dapat juga terjadi antara orang pribadi dan badan;</em></p>			<p><em>Huruf b</em><br>			<em>Hubungan<em> </em>istimewa</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>juga</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>penguasaan</em><em> </em><em>melalui</em><em> </em><em>manajemen atau penggunaan teknologi, walaupun tidak terdapat hubungan kepemilikan. Hubungan istimewa dianggap ada apabila satu atau lebih perusahaan berada di bawah penguasaan yang sama. Demikian juga hubungan antara beberapa perusahaan yang berada dalam penguasaan yang sama tersebut.</em></p>			<p><em>Huruf c<br>			Yang dimaksud dengan hubungan keluarga sedarah dalam garis keturunan lurus satu derajat adalah ayah, ibu, dan anak, sedangkan hubungan keluarga sedarah dalam garis keturunan ke samping satu derajat adalah saudara. Yang dimaksud dengan keluarga semenda dalam garis keturunan lurus satu derajat adalah mertua dan anak tiri, sedangkan hubungan keluarga semenda dalam garis keturunan ke samping satu derajat adalah ipar;</em>”</p>			<p>bahwa menurut pendapat Majelis,makna Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 adalah jika terdapat hubungan istimewa maka ada kemungkinan terjadi penghindaran pajak melalui :<br>			a. Transaksi yang tidak wajar,b. Transaksi wajar tetapi nilainya tidak wajar;</p>			<p>bahwa semakin tinggi level hubungan istimewa maka semakin tinggi kemungkinan terdapat kedua macam transaksi tersebut diatas;</p>			<p>bahwa berdasarkan Penjelasan Tertulis Pemohon Banding tanggal 5 Februari 2013, tanpa nomor, diketahui bahwa Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa skema Pemegang saham Pemohon Banding adalah sebagai berikut:</p>			<p style='text-align:center'><img alt='' src='data:image/png;base64,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' style='height:215px; width:591px'></p>			<p>bahwa dengan demikian Majelis berkesimpulan bahwa Pemohon Banding dan Halliburton Energy Services Inc. mempunyai hubungan istimewa dan tidak dapat disebut sebagai pihak-pihak yang independen;</p>			<p>bahwa berdasarkan <em>Amanded and Restated Tech Fee Agreement</em>(P.5) Pemohon Banding dapat memanfaatkan dan menggunakan seluruh <em>patented and non-patented technology, software, technical and non-technical trade secrets and know-how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Majelis, dalam halpemilik dan pengguna teknologi adalah pihak-pihak independen (tidak ada hubungan istimewa), maka pengguna teknologi mau membayar <em>Technical Assistance Fee </em>kepada pemilik teknologi karena pengguna teknologi mengharapkan keuntungan (profit) dari penjualan jasa yang menggunakan teknologi tersebut;</p>			<p>bahwa dengan kata lain, pengguna teknologi tidak akan menjual jasa yang menurut perhitungannya penjualan jasa tersebut tidak dapat memberikan keuntungan;</p>			<p>bahwa menurut pendapat Majelis, pertimbangan utama perhitungan besarnya <em>Technical</em><em> </em><em>Assistance Fee </em>yang dibayarkan pengguna teknologi didasarkan seberapa besar keuntungan yang diharapkannya dari penjualan jasa pihak pengguna teknologi tersebut;</p>			<p>bahwa setelah mengetahui keuntungan yang diharapkan, kemudian Pemohon Banding menghitung besaran <em>Technical Assistance Fee </em>yang pantas untuk dibayarkan;</p>			<p>bahwa besaran besarnya <em>Technical Assistance Fee </em>yang pantas dibayarkan tersebut dapat dituangkan dalam bentuk hitungan sekian persen dari peredaran usaha atau sekian persen dari nilai produksi atau sekian persen dari keuntungan, atau sejumlah tertentu dan sebagainya;</p>			<p>bahwa setelah pembayaran besarnya <em>Technical Assistance Fee </em>tentu masih ada keuntungan yang diharapkan untuk dibagikan kepada pemilik/pemegang saham;</p>			<p>bahwa sampai dengan persidangan selesai, Pemohon Banding tidak pernah memberikan data estimasi/proyeksi keuntungan yang akan diperoleh Pemohon Banding dalam melakukan kegiatan usahanya, sehingga Majelis berpendapat besaran pembayaran besarnya <em>Technical Assistance Fee </em>tersebut tidak dapat dinilai kewajarannya;</p>			<p>bahwa berdasarkan Analisa atas Laporan Keuangan untuk Perpajakan yang diserahkan Pemohon Banding dalam persidangan terdapat fakta Penghasilan Neto Komersial Pemohon Banding sebagai berikut :</p>			<div class='tablewrap'><table align='center' border='1' cellpadding='0' cellspacing='0'>				<tbody>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>Tahun</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2002</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2003</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2004</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2005</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2006</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2007</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2008</strong></div>						</div></td>					</tr>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div>Net Income&nbsp;before Tax&nbsp;(USD)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div style='text-align: right;'>(4,900,700.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(3,673,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(7,419,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(2,903.000,00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(1,053,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>5,774,000.00</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(3,148,000.00)</div>						</div></td>					</tr>				</tbody>			</table></div>			<p>bahwa berdasarkan fakta dan data tersebut, Pemohon Banding hanya mengalami laba secara komersial pada Tahun 2007 sebesar USD 5,774,000.00, sementara Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 Pemohon Banding mengalami kerugian secara komersial;</p>			<p>bahwa akumulasi kerugian komersial (accumulated deficit) Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 berjumlah USD 23,096,700.00;</p>			<p>bahwa dalam kondisi demikian menurut pendapat Majelis,pembayaran<em>Intercompany Technical Assistance Fee</em>secara terus-menerus setiap tahun yang dilakukan oleh Pemohon Banding kepada Halliburton Energy Services Inc. yang merupakan pihak yang memiliki hubungan istimewa adalah sesuatu yang tidak wajar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan-pertimbangan Majelis sebagaimana tersebut di atas, Majelisberkesimpulan bahwa koreksi Terbanding atas <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00tetap dipertahankan;</p>			<p>koreksi positif biaya Enterprise Resource Planning (ERP) sebesar USD791,784.00</p>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi positif biaya Enterprise Resource Planning (ERP) sebesar USD791,784.00 karena:</p>			<p style='margin-left:40px'>- dibebankan oleh Pemohon Banding berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services, Inc, USA (HES, Inc) yang berlaku efektif 01 Januari 2002;<br>			-biaya Enterprise Resource Planning (ERP) Fee (acc.610302) yang dibebankan oleh Pemohon Banding adalah atas penggunaan software platform standard berbasis SAP Integrated ERP yang dapat diakses/dimanfaatkan/digunakan untuk mendukung operasional perusahaan;<br>			-perhitungan biaya Enterprise Resource Planning (ERP) Fee sesuai dengan article V Global ERP Platform Agreement dengan menggunakan dua metode perhitungan mana yang lebih rendah yaitu antara Calculation of Annual Cap dengan Calculation of Provisional Fee;<br>			-berdasarkan dua metode ini, perhitungan biaya Enterprise Resource Planning (ERP) Fee didasarkan kepada Operating Income dan Third Party Revenue bukan atas dasar nilai fee tertentu yang telah disepakati oleh kedua pihak yang berjanji atas penggunaan software demikian juga metode penghitungan ini tidak berkaitan langsung dengan ada dan tidaknya upgrading, maintenance dan service yang diberikan untuk software yang bersangkutan sehingga tidak dapat diyakini pengeluaran biaya Enterprise Resource Planning (ERP) Fee dalam rangka biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana disebutkan dalam Pasal 6 ayat (1) huruf a Undang-Undang Nomor 17 Tahun 2000;<br>			-bahwa ketidaklaziman atas biaya Enterprise Resource Planning (ERP) Fee yang dibayarkan kepada HES,Inc. ini pada prinsipnya adalah merupakan pembagian laba (dividen) sesuai dengan Pasal 4 ayat (1) huruf g Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Pemohon Banding, fakta dan bukti kebenaran adanya biaya Enterprise Resource Planning Fee tersebut telah Pemohon Banding berikan kepada Terbanding selama pemeriksaan termasuk kontrak dengan vendor, invoice vendor, invoice dan pembukuan, kontrak dengan customer, invoice penghasilan kepada customer, pembayaran PPN atas pemanfaatan BKP/JKP tidak berwujud dari luar daerah pabean, pembayaran PPh Pasal 26 terkait, dan juga Audit Report dan Transfer Pricing Study;</p>			<p>bahwa menurut Pemohon Banding, wewenang berdasarkan Pasal 18 ayat (3) Undang-UndangNomor7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor17 Tahun 2000 yang dijadikan dasar hukum Terbanding dalam melakukan koreksi, tidak dapat digunakan Terbanding untuk meniadakan biaya tersebut;</p>			<p>bahwa menurut Pemohon Banding, koreksi Terbanding dalam hal ini melanggar Pasal 18 ayat (3) Undang-UndangNomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 karena justru koreksi tersebut menjadi tidak wajar karena mengabaikan transaksi yang terjadi dan tidak dapat membuktikan mengenai kewajaran peniadaan biaya tersebut;</p>			<p>bahwa menurut Pemohon Banding, dengan adanya kontrak dengan customer, invoice kepada customer, pengelolaan inventory dan seluruh resources perusahaan, SPT Pajak Penghasilan, SPT Pajak Pertambahan Nilai merupakan bukti adanya manfaat atas biaya Enterprise Resource Planning tersebut;bahwa menurut pendapat Majelis, Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :</p>			<p>“<em>Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan pengurangan serta menentukan utang sebagai modal untuk menghitung besarnya Penghasilan Kena Pajak bagi Wajib Pajak yang mempunyai hubungan istimewa dengan Wajib Pajak lainnya sesuai dengan kewajaran dan kelaziman usaha yang tidak dipengaruhi oleh hubungan istimewa</em>”</p>			<p>&nbsp;</p>			<p>bahwa Penjelasan Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>maksud diadakannya ketentuan ini adalah untuk mencegah terjadinya penghindaran pajak, yang dapat terjadi karena adanya hubungan istimewa. Apabila terdapat hubungan istimewa, kemungkinan dapat terjadi penghasilan dilaporkan kurang dari semestinya ataupun pembebanan biaya melebihi dari yang seharusnya. Dalam hal demikian Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan atau biaya sesuai dengan keadaan seandainya di antara para Wajib Pajak tersebut tidak terdapat hubungan istimewa. Dalam menentukan kembali jumlah penghasilan dan atau biaya tersebut dapat dipakai beberapa pendekatan, misalnya data pembanding, alokasi laba berdasar fungsi atau peran serta dari Wajib Pajak yang mempunyai hubungan istimewa dan indikasi serta data lainnya. Demikian pula kemungkinan terdapat penyertaan modal secara terselubung, dengan menyatakan penyertaan modal tersebut sebagai utang, maka Direktur Jenderal Pajak berwenang untuk menentukan utang tersebut sebagai modal perusahaan. Penentuan tersebut dapat dilakukan misalnya melalui indikasi mengenai perbandingan antara modal dengan utang yang lazim terjadi antara para pihak yang tidak dipengaruhi oleh hubungan istimewa atau berdasar data atau indikasi lainnya. Dengan demikian bunga yang dibayarkan sehubungan dengan utang yang dianggap sebagai penyertaan modal itu tidak diperbolehkan untuk dikurangkan, sedangkan bagi pemegang saham yang menerima atau memperolehnya dianggap sebagai dividen yang dikenakan pajak.</em>”</p>			<p>bahwa Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 menyatakan :<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>sebagaimana</em><em> </em><em>dimaksud</em><em> </em><em>dalam</em><em> </em><em>ayat</em><em> </em><em>(3)</em><em> </em><em>dan</em><em> </em><em>(3a),</em><em> </em><em>Pasal</em><em> </em><em>8</em><em> </em><em>ayat</em><em> </em><em>(4),</em><em> </em><em>Pasal</em><em> </em><em>9</em><em> </em><em>ayat (1) huruf f, dan Pasal 10 ayat (1) dianggap ada apabila :<br>			a. <em> </em>Wajib Pajak mempunyai penyertaan modal langsung atau tidak langsung paling rendah 25% (dua puluh lima persen) pada Wajib Pajak lain, atau hubungan antara Wajib Pajak dengan penyertaan paling rendah 25% (dua puluh lima persen) pada dua Wajib Pajak atau lebih, demikian pula hubungan antara dua Wajib Pajak atau lebih yang disebut terakhir; atau<br>			b. Wajib<em> </em>Pajak<em> </em>menguasai</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>lainnya</em><em> </em><em>atau</em><em> </em><em>dua</em><em> </em><em>atau</em><em> </em><em>lebih</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>berada</em><em> </em><em>di</em><em> </em><em>bawah penguasaan yang sama baik langsung maupun tidak langsung; atau</em><br>			c. <em>terdapat hubungan keluarga baik sedarah maupun semenda dalam garis keturunan lurus dan atau ke samping satu derajat;</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan:<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>di</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>ketergantungan</em><em> </em><em>atau</em><em> </em><em>keterikatan</em><em>satu dengan yang lain yang disebabkan karena :<br>			a. <em> </em>kepemilikan atau penyertaan modal;b. </em><em> </em><em>adanya penguasaan melalui manajemen atau penggunaan teknologi.<br>			Selain karena hal-hal tersebut di atas, hubungan istimewa di antara Wajib Pajak orang pribadi dapat pula terjadi karena adanya hubungan darah atau karena perkawinan;</em></p>			<p><em>Huruf a<br>			Hubungan istimewa dianggap ada apabila terdapat hubungan kepemilikan yang berupa penyertaan modal sebesar 25% (dua puluh lima persen) atau lebih secara langsung ataupun tidak langsung. Misalnya, PT A mempunyai 50% (lima puluh persen) saham PT B. Pemilikan saham oleh PT A merupakan penyertaan langsung. Selanjutnya apabila PT B tersebut mempunyai 50% (lima puluh persen) saham PT C, maka PT A sebagai pemegang saham PT B secara tidak langsung mempunyai penyertaan pada PT C sebesar 25% (dua puluh lima persen). Dalam hal demikian antara PT A, PT B dan PT C dianggap terdapat hubungan istimewa. Apabila PT A juga memiliki 25% (dua puluh lima persen) saham PT D, maka antara PT B, PT C dan PT D dianggap terdapat hubungan istimewa. Hubungan kepemilikan seperti tersebut di atas dapat juga terjadi antara orang pribadi dan badan;</em></p>			<p><em>Huruf b</em><br>			<em>Hubungan istimewa antara Wajib Pajak dapat juga terjadi karena penguasaan melalui manajemen atau penggunaan teknologi, walaupun tidak terdapat hubungan kepemilikan. Hubungan istimewa dianggap ada apabila satu atau lebih perusahaan berada di bawah penguasaan yang sama. Demikian juga hubungan antara beberapa perusahaan yang berada dalam penguasaan yang sama tersebut.</em></p>			<p><em>Huruf c</em><br>			<em>Yang dimaksud dengan hubungan keluarga sedarah dalam garis keturunan lurus satu derajat adalah ayah, ibu, dan anak, sedangkan hubungan keluarga sedarah dalam garis keturunan ke samping satu derajat adalah saudara. Yang dimaksud dengan keluarga semenda dalam garis keturunan lurus satu derajat adalah mertua dan anak tiri, sedangkan hubungan keluarga semenda dalam garis keturunan ke samping satu derajat adalah ipar;</em>”</p>			<p>bahwa menurut pendapat Majelis,makna Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 adalah jika terdapat hubungan istimewa maka ada kemungkinan terjadi penghindaran pajak melalui :<br>			a. Transaksi yang tidak wajar,b. Transaksi wajar tetapi nilainya tidak wajar;</p>			<p>bahwa semakin tinggi level hubungan istimewa maka semakin tinggi kemungkinan terdapat kedua macam transaksi tersebut diatas;</p>			<p>bahwa berdasarkan Penjelasan Tertulis Pemohon Banding tanggal 5 Februari 2013, tanpa nomor, diketahui bahwa Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;bahwa skema Pemegang saham Pemohon Banding adalah sebagai berikut :<img alt='' src='data:image/png;base64,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' style='height:208px; width:636px'></p>			<p>bahwa dengan demikian Majelis berkesimpulan bahwa Pemohon Banding dan Halliburton Energy Services Inc. mempunyai hubungan istimewa dan tidak dapat disebut sebagai pihak-pihak yang independen;</p>			<p>bahwa biaya Enterprise Resource Planning (ERP) dibayar oleh Pemohon Banding kepada Halliburton Energy Services Inc. berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services Inc.;</p>			<p>bahwa Pemohon Banding membayar biaya Enterprise Resource Planning (ERP) kepada Halliburton Energy Services Inc. atas penggunaan <em>software </em>yang dikembangkan oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Majelis, dalam halpemilik dan pengguna Teknologi (<em>software) </em>adalah pihak- pihak independen (tidak ada hubungan istimewa), maka pengguna teknologi mau membayar biaya Enterprise Resource Planning (ERP)kepada pemilik teknologi karena pengguna teknologi mengharapkan keuntungan (profit) dari penjualan jasa yang menggunakan teknologi tersebut;</p>			<p>bahwa dengan kata lain, pengguna teknologi tidak akan menjual jasa yang menurut perhitungannya penjualan jasa tersebut tidak dapat memberikan keuntungan;</p>			<p>bahwa menurut pendapat Majelis, pertimbangan utama perhitungan besarnya biaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan pengguna teknologi didasarkan seberapa besar keuntungan yang diharapkannya dari penjualan jasa pihak pengguna teknologi tersebut;</p>			<p>bahwa setelah mengetahui keuntungan yang diharapkan, kemudian Pemohon Banding menghitung besaran <em>Enterprise Resource Planning (ERP)</em>yang pantas untuk dibayarkan;</p>			<p>bahwa besaran besarnya biaya <em>Enterprise Resource Planning (ERP)</em>yang pantas dibayarkan tersebut dapat dituangkan dalam bentuk hitungan sekian persen dari peredaran usaha atau sekian persen dari nilai produksi atau sekian persen dari keuntungan, atau sejumlah tertentu dan sebagainya;</p>			<p>bahwa setelah pembayaran besarnya biaya <em>Enterprise Resource Planning (ERP) </em>tentu masih ada keuntungan yang diharapkan untuk dibagikan kepada pemilik/pemegang saham;</p>			<p>bahwa sampai dengan persidangan selesai, Pemohon Banding tidak pernah memberikan data estimasi/proyeksi keuntungan yang akan diperoleh Pemohon Banding dalam melakukan kegiatan usahanya, sehingga Majelis berpendapat besaran pembayaran besarnya biaya <em>Enterprise Resource Planning (ERP)</em>tersebut tidak dapat dinilai kewajarannya;bahwa berdasarkan Analisa atas Laporan Keuangan untuk Perpajakan yang diserahkan Pemohon Banding dalam persidangan terdapat fakta Penghasilan Neto Komersial Pemohon Banding sebagai berikut :</p>			<div class='tablewrap'><table align='center' border='1' cellpadding='0' cellspacing='0'>				<tbody>					<tr>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>Tahun</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2002</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2003</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2004</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2005</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2006</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2007</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2008</div>						</div></td>					</tr>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div>Net Income&nbsp;before Tax(USD)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(4,900,700.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(3,673,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(7,419,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(2,903.000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(1,053,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>5,774,000.00</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(3,148,000.00)</div>						</div></td>					</tr>				</tbody>			</table></div>			<p>bahwa berdasarkan fakta dan data tersebut, Pemohon Banding hanya mengalami laba secara komersial pada Tahun 2007 sebesar USD 5,774,000.00, sementara Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 Pemohon Banding mengalami kerugian secara komersial;</p>			<p>bahwa akumulasi kerugian komersial (accumulated deficit) Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 berjumlah USD 23,096,700.00;</p>			<p>bahwa dalam kondisi demikian menurut pendapat Majelis,pembayaran biaya <em>Enterprise Resource Planning (ERP)</em>secara terus-menerus setiap tahun yang dilakukan oleh Pemohon Banding kepada Halliburton Energy Services Inc. yang merupakan pihak yang memiliki hubungan istimewa adalah sesuatu yang tidak wajar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan-pertimbangan Majelis sebagaimana tersebut di atas, Majelisberkesimpulan bahwa koreksi Terbanding atas biaya <em>Enterprise Resource Planning (ERP) </em>sebesar USD791,784.00 tetap dipertahankan;</p>			<p>bahwa berdasarkan hasil pemeriksaan, pertimbangan dan kesimpulan Majelis atas koreksi <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 dan biaya <em>Enterprise Resource Planning (ERP) </em><em> </em>sebesar USD791,784.00sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015, dapat disimpulkan bahwa kedua biaya tersebut merupakan biaya yang tidak wajar dan kewajaran biaya tersebut tidak dapat diyakini oleh Majelis sehingga Majelis tetap mempertahankan kedua koreksi tersebut;</p>			<p>bahwa Majelis berkesimpulan <em>Intercompany Technical Assistance Fee</em>danbiaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan Pemohon Banding kepada pihak Halliburton Energy Services Inc. bukan merupakan objek yang terutang Pajak Pertambahan Nilai sehingga koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tetap dipertahankan;</p>			<p><strong>Pendapat Berbeda (Dissenting Opinion)</strong></p>			<p>bahwa terhadap koreksinegatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00), Hakim Anggota Sartono, S.H., M.Si.memberikan pendapat dan pertimbangan yang berbeda sebagai berikut :</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si.,Terbanding melakukan koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00)karena merupakan konsistensi dari adanya koreksi pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) Fee pada sengketa koreksi Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si., Terbanding menetapkan pembayaran objek sebesar Rp49.191.835.605,00 bukan merupakan pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP), melainkan pembayaran dividen kepada pemegang saham sehingga bukan merupakan objek PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean;</p>			<p>bahwa oleh karena koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak terwujud Dari Luar Daerah Pabean bersumber dari koreksi positif atas kedua biaya tersebut di atas, maka pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. terhadap sengketa ini mengikuti hasil pemeriksaan Hakim Anggota Sartono, S.H., M.Si. terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) pada sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008 berupa koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) telah diputus oleh Pengadilan Pajak dengan Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015;</p>			<p>bahwa hasil pemeriksaan, pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015 adalah sebagai berikut :<br>			koreksi positif Intercompany Technical Assistance Fee sebesar USD5,349,708.00</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., berdasarkan <em>Amanded and Restated Tech Fee Agreement </em>tanggal 01 Januari 2002 (P.5)<em>, </em>Pemohon Banding membayar <em>Intercompany Technical Assistance Fee</em>kepada Halliburton Energy Services Inc. yang pada Tahun Pajak 2008 dibayar sebesar USD5,349,708.00 adalah pembayaran atas penggunaan atau pemanfaatan seluruh <em>patented and non-patented technology, software, technical and non-technical trade secrets and know- how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen berupa <em>Amanded and Restated Tech Fee Agreement</em>(P.5)<em>, </em>tagihan/invoice (P.72), pembayaran PPN Jasa Kena Pajak Luar Negeri (P.60), Pemotongan PPh Pasal 26 (P.66), General Ledger – Intercompany Expenses Period January to December 2008 (P.65), G/L Account Bo.141200 Intercompany Advances - Company Code 8054 (P.67) dan Transfer Pricing Study Report (P.59), Hakim Anggota Sartono, S.H., M.Si. berkeyakinan bahwa pembayaran Intercompany Technical Assistance Fee kepada Halliburton Energy Services Inc. bukan merupakan pembayaran dividen (pembagian laba) melainkan pembayaran sehubungan dengan penggunaan : <em>patented and non-patented technology, software, technical and non-technical trade secrets and know-how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., jasa yang dilakukan oleh Pemohon Banding yang terkait dengan bidang pertambangan minyak dan gas bumi sangat membutuhkan teknologi, metode, serta perangkat lain yang spesifik yang tidak dimiliki oleh Pemohon Banding dan hanya dimiliki oleh pihak tertentu;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., pembayaran <em>Intercompany Technical Assistance Fee</em>dapat dibebankan sebagai biaya sepanjang biaya tersebut dikeluarkan dalam rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;bahwa berdasarkan hal-hal tersebut, Hakim Anggota Sartono, S.H., M.Si. berpendapat bahwa, <em>Intercompany Technical Assistance Fee </em>yang dibayarkan kepada Halliburton Energy Services Inc. merupakan biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa antara Pemohon Banding dengan Halliburton Energy Services Inc. memiliki hubungan istimewa karena Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa transaksi antar pihak yang memiliki hubungan istimewa diatur dalam Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17Tahun 2000;</p>			<p>bahwa Pasal 18 ayat (3) a quo tidak menghilangkan eksistensi biaya yang dikeluarkan oleh Pemohon Banding dalam rangka pembayaran kepada pihak yang memiliki hubungan istimewa, namun biaya tersebut harus wajar dan lazim sebagaimana transaksi dengan pihak-pihak yang tidak memiliki hubungan istimewa serta biaya tersebut harus terkait dengan kegiatan usaha Pemohon Banding;</p>			<p>bahwa Terbanding seharusnya menentukan biaya yang wajar yang harus dikeluarkan oleh Pemohon Banding berdasarkan data pembanding yang dimiliki oleh Terbanding yaitu transaksi atau biaya yang sama yang dibayar oleh Pemohon Banding atau perusahaan lain kepada pihak-pihak yang tidak memiliki hubungan istimewa dengan Pemohon Banding ataupun dengan perusahaan lain tersebut;</p>			<p>bahwa dalam persidangan, Terbanding tidak dapat menunjukkan data pembanding tersebut sehingga tidak dapat menentukan berapa nilai wajar yang seharusnya dibayarkan oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. untuk membayar <em>Intercompany Technical Assistance Fee</em>;</p>			<p>bahwa berdasarkan Transfer Pricing Study Report, Hakim Anggota Sartono, S.H., M.Si. dapat meyakini bahwa pembayaran <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan transaksi yang wajar dan lazim dalam dunia usaha Pemohon Banding di bidang jasa pertambangan minyak dan gas bumi;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen-dokumen dalam persidangan serta berdasarkan pertimbangan-pertimbangan Hakim Anggota Sartono, S.H., M.Si., Hakim Anggota Sartono, S.H., M.Si. berkesimpulan <em>bahwa Intercompany Technical Assistance Fee </em>yang dibayarkan sebesar USD5,349,708.00 oleh Pemohon Banding telah sesuai dengan Pasal 6 ayat (1) huruf a dan Pasal 18 ayat (3)Undang-Undang Nomor 7Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 sehingga koreksi Terbanding sebesar USD5,349,708.00 tidak dapat dipertahankan;</p>			<p>Koreksi positif biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em> </em><em>(ERP)</em><em> </em>sebesar USD791,784.00</p>			<p>bahwa menurut Hakim Anggota Sartono, S.H., M.Si.,biaya <em>Enterprise Resource Planning (ERP)</em>dibayar oleh Pemohon Banding kepada Halliburton Energy Services Inc. berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services Inc.;</p>			<p>bahwa Pemohon Banding membayar biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em> </em><em>(ERP)</em>kepada Halliburton Energy Services Inc. atas penggunaan <em>software</em><em> </em>yang dikembangkan oleh Halliburton Energy Services Inc.;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen berupa Global ERP Platform Agreement (P.7)<em>, </em>tagihan/invoice (P.62), pembayaran PPN BKP tak berwujud dari luar pabean (P.47), Pemotongan PPh Pasal 26 (P.63), ERP Development Summary (P.70), General Ledger-Miscellaneous Expenses (P.64) dan Transfer Pricing Study Report (P.59), Hakim Anggota Sartono, S.H., M.Si. berkeyakinan bahwa pembayaran <em>Enterprise Resource Planning (ERP)</em>kepada Halliburton Energy Services Inc. bukan merupakan pembayaran dividen (pembagian laba) melainkan pembayaran sehubungan dengan penggunaan software yang dikuasai dan dimiliki oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., Pemohon Banding sebagai anak perusahaan dari grup Halliburton sangat membutuhkan suatu sistem yang terkoneksi dengan grup usahanya serta sistem yang akan mempermudah operasional perusahaan;</p>			<p>bahwa berdasarkan Global ERP Platform Agreement, sistem yang disediakan dari software tersebut adalah : <em>Financial Accounting, Controlling (Cost Centre Accounting), Fixed Assets Management, Project Sistem, Materials Management, Production Planning, Sales and Distribution, Plant Maintenance, Quality Management, dan Human Resources</em>;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., pembayaran <em>Enterprise Resource Planning (ERP)</em>dapat dibebankan sebagai biaya sepanjang biaya tersebut dikeluarkan dalam rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa berdasarkan hal-hal tersebut di atas, Hakim Anggota Sartono, S.H., M.Si.berpendapat bahwa biaya <em>Enterprise Resource Planning (ERP) </em>yang dibayarkan kepada Halliburton Energy Services Inc. merupakan biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa antara Pemohon Banding dengan Halliburton Energy Services Inc. memiliki hubungan istimewa karena Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa transaksi antar pihak yang memiliki hubungan istimewa diatur dalam Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa Pasal 18 ayat (3) a quo tidak menghilangkan eksistensi biaya yang dikeluarkan oleh Pemohon Banding dalam rangka pembayaran kepada pihak yang memiliki hubungan istimewa, namun biaya tersebut haruslah wajar dan lazim sebagaimana transaksi dengan pihak-pihak yang tidak memiliki hubungan istimewa danbiaya tersebut terkait dengan kegiatan usaha Pemohon Banding;</p>			<p>bahwa Terbanding seharusnya menentukan biaya yang wajar yang harus dikeluarkan oleh Pemohon Banding berdasarkan data pembanding yang dimiliki oleh Terbanding yaitu transaksi atau biaya yang sama yang dibayar oleh Pemohon Banding atau perusahaan lain kepada pihak-pihak yang tidak memiliki hubungan istimewa dengan Pemohon Banding ataupun dengan perusahaan lain tersebut;</p>			<p>bahwa dalam persidangan, Terbanding tidak dapat menunjukkan data pembanding tersebut sehingga tidak dapat menentukan berapa nilai wajar yang seharusnya dibayarkan oleh Pemohon Bandingkepada pihak Halliburton Energy Services Inc. untuk membayar biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em>(ERP);</em></p>			<p>bahwa berdasarkan Transfer Pricing Study Report, Hakim Anggota Sartono, S.H., M.Si. dapat meyakini bahwa pembayaran biaya <em>Enterprise Resource Planning (ERP) </em>sebesar USD791,784.00oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan transaksi yang wajar dan lazim bagi Pemohon Banding sebagai salah satu anak perusahaan dari grup Halliburton;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen-dokumen dalam persidangan serta berdasarkan pertimbangan-pertimbangan Hakim Anggota Sartono, S.H., M.Si., Hakim Anggota Sartono, S.H., M.Si. berkesimpulan bahwa biaya <em>Enterprise Resource Planning (ERP) </em>yang dibayarkan sebesar USD791,784.00oleh Pemohon Banding telah sesuai dengan Pasal 6 ayat (1) huruf a dan Pasal 18 ayat (3)Undang-Undang Nomor 7Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 sehingga koreksi Terbanding sebesar USD791,784.00tidak dapat dipertahankan;</p>			<p>bahwa berdasarkan hasil pemeriksaan, pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. atas koreksi <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 dan biaya <em>Enterprise Resource Planning (ERP) </em><em> </em>sebesar USD791,784.00sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015, dapat disimpulkan bahwa kedua biaya tersebut merupakan biaya yang wajar dan lazim sesuai dengan Pasal 18 ayat (3) Undang-Undang Nomor 7Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 dan dikeluarkan rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang a quo sehingga Hakim Anggota Sartono, S.H., M.Si. tidak dapat mempertahankan kedua koreksi tersebut;</p>			<p>bahwa Hakim Anggota Sartono, S.H., M.Si. berkesimpulan <em>Intercompany Technical Assistance Fee</em>danbiaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan objek yang terutang Pajak Pertambahan Nilai sehingga koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tidak dapat dipertahankan;</p>			<p>bahwa menurut pendapat Majelis, Terbanding melakukan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 karena Terbanding menetapkan objek sebesar Rp49.191.835.605,00 sebagai objek yang bukan merupakan Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean, sehingga PPN yang telah disetor oleh Pemohon Banding atas objek tersebut tidak dapat dikreditkan sebagai Pajak Masukan;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding nyata-nyata telah melakukan pembayaran PPN dan melaporkan dalam SPT Masa PPN atas pemanfaatan JKP dan/atau BKP Tidak Berwujud dari Luar Daerah Pabean dan pembayaran PPN atas pemanfaatan jasa dari Luar Daerah Pabean yang Pemohon Banding lakukan merupakan Kredit Pajak yang sah;</p>			<p>bahwa Majelis melakukan pemeriksaan terhadap dokumen dan bukti-bukti berupa Surat Setoran Pajak (SSP) pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean atas lawan transaksi Halliburton Energy Services Inc. (P.6) dan Invoice dari Halliburton Energy Services Inc. terkait dengan pembayaran <em>Intercompany Technical Assistance Fee </em>dan <em>Entreprise Resource Planning (ERP) Fee</em>(P.22);</p>			<p>bahwa dari pemeriksaan Majelis, diketahui bahwa Surat Setoran Pajak sebesar Rp19.059.940.656,00 merupakan pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean yang dibayarkan ke kas negara melalui Citibank Jakarta;</p>			<p>bahwa dengan tidak diakuinya objek sebesar Rp49.191.835.605,00 sebagai Dasar Pengenaan Pajak PPN BKP tidak berwujud dari Luar Daerah Pabean oleh Terbanding, tidak serta merta pajak yang telah disetor oleh Pemohon Banding secara sah kepada kas negara atas objek sebesar Rp49.191.835.605,00 menjadi tidak sah dan tidak dapat dikreditkan;</p>			<p>bahwa menurut pendapat Majelis, dengan berkurangnya Dasar Pengenaan Pajak PPN BKP tidak berwujud dari Luar Daerah Pabean yang terutang pajak menurut Terbanding dan Pemohon Banding sudah membayar seluruh PPN atas objek tersebut, maka telah terjadi kelebihan pembayaran pajak oleh Pemohon Banding;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan Majelis sebagaimana tersebut di atas, Majelis berkesimpulan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 tidak dapat dipertahankan;</p>			<p><strong>Pendapat Berbeda (Dissenting Opinion)</strong></p>			<p>bahwa koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00, Hakim AnggotaSartono, S.H., M.Si.memberikan pendapat dan pertimbangan yang berbeda sebagai berikut :</p>			<p>bahwa. kesimpulan Hakim AnggotaSartono, S.H., M.Si atas koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tidak dapat mempertahankan koreksi tersebut;</p>			<p>bahwa berdasarkan hal tersebut, Dasar Pengenaan Pajak menurut Hakim AnggotaSartono, S.H., M.Si adalah Dasar Pengenaan Pajak berdasarkan SPT Masa PPN Pemohon Banding sebesar Rp190.599.406.560,00 dan PPN yang terutang adalah sebesar Rp.19.059.940.656,00;</p>			<p>bahwa Hakim AnggotaSartono, S.H., M.Si melakukan pemeriksaan terhadap dokumen dan bukti-bukti berupa Surat Setoran Pajak (SSP) pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean atas lawan transaksi Halliburton Energy Services Inc. (P.6) dan Invoice dari Halliburton Energy Services Inc. terkait dengan pembayaran <em>Intercompany Technical Assistance Fee </em>dan <em>Entreprise Resource Planning (ERP) Fee</em>(P.22);</p>			<p>bahwa dari pemeriksaan Hakim AnggotaSartono, S.H., M.Si, diketahui bahwa Surat Setoran Pajak sebesar Rp19.059.940.656,00 merupakan pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean yang dibayarkan ke kas negara melalui Citibank Jakarta;</p>			<p>bahwa dengan demikian Kredit Pajak sebesar Rp19.059.940.656,00 merupakan kredit pajak yang sah dan dapat dikreditkan dengan Pajak Masukan sepanjang memenuhi ketentuan perundang-undangan perpajakan yang berlaku;</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si, pengkreditan Pajak Masukan sebesar Rp19.059.940.656,00 telah sesuai dengan Pasal 9 ayat (2), 9 ayat (3) 9 ayat (9) dan Pasal 13 ayat (6) Undang-Undang Nomor 8 Tahun 1983 tentang Pajak Pertambahan Nilai Barang dan Jasa dan Pajak Penjualan Atas Barang Mewah sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 jo. Keputusan Direktur Jenderal Pajak Nomor : KEP-522/PJ/2000 tanggal 6 Desember 2000 tentang Dokumen-Dokumen Tertentu Yang Diperlakukan Sebagai Faktur Pajak Standar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan Hakim AnggotaSartono, S.H., M.Si sebagaimana tersebut di atas, Hakim AnggotaSartono, S.H., M.Si berkesimpulan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 tidak dapat dipertahankan;</p>			</div></td>		</tr>	</tbody></table><p style='text-align:center'><strong>MENIMBANG</strong><br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai Tarif Pajak;<br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai Sanksi Administrasi, kecuali bahwa besarnya sanksi administrasi tergantung pada penyelesaian sengketa lainnya;<br>bahwa atas hasil pemeriksaan dalam persidangan dan berdasarkan suara terbanyak, Majelis berketetapan untuk menggunakan kuasa Pasal 80 ayat (1) huruf b Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak untuk mengabulkan sebagian banding Pemohon Banding dengan Dasar Pengenaan Pajak menurut Majelis sebesar Rp141.407.570.955,00 dan Kredit Pajak menurut Majelis sebagai berikut :<br>Kredit Pajak menurut Terbanding Rp 14.140.757.095,00<br>Koreksi yang tidak dapat dipertahankan Rp 4.919.183.561,00<br>Kredit Pajak menurut Majelis Rp 19.059.940.656,00</p><p style='text-align:center'><strong>MENGINGAT</strong><br>Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, dan ketentuan perundang-undangan lainnya serta peraturan hukum yang berlaku dan yang berkaitan dengan sengketa ini;</p><p style='text-align:center'><strong>MEMUTUSKAN</strong><br><strong>Mengabulkan sebagian&nbsp;</strong>banding Pemohon Banding terhadap Keputusan Terbanding Nomor: KEP-3253/WPJ.07/2011 tanggal 22 Desember 2011tentang Keberatan atas Surat Ketetapan Pajak Nihil Pajak Pertambahan Nilai Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 Nomor: 00005/567/08/056/10 tanggal 30 September 2010, atas nama : <strong>PT XXX</strong>, dengan perhitungan menjadi sebagai berikut :<br>Dasar Pengenaan Pajak :Pemanfaatan BKP tidak berwujud dari Luar Daerah Pabean Rp141.407.570.955,00<br>PPN yang harus dipungut / dibayar sendiri Rp 14.140.757.095,00<br>Jumlah Pajak yang dapat diperhitungkan (Rp 19.059.940.656,00)<br>Jumlah PPN Kurang (lebih) dibayar (Rp 4.919.183.561,00)</p><p style='text-align:center'>Demikian diputus di Jakarta berdasarkan suara terbanyak setelah pemeriksaan dalam persidangan yang dicukupkan pada hari Rabu tanggal 06 Februari 2013, oleh Hakim Majelis XV Pengadilan Pajak yang ditunjuk dengan Penetapan Ketua Pengadilan Pajak Nomor : Pen.00843/PP/PM/VIII/2012 tanggal 03 Agustus 2012 dengan susunan Majelis dan Panitera Pengganti sebagai berikut :<br>Drs. Tonggo Aritonang, Ak., M.Sc. Sebagai Hakim Ketua,<br>Drs. Didi Hardiman, Ak. Sebagai Hakim Anggota,<br>Sartono, S.H., M.Si. Sebagai Hakim Anggota,<br>M.R. Abdi Nugroho Sebagai Panitera Pengganti,</p><p style='text-align:center'>Putusan Nomor : Put.59334/PP/M.XV/16/2015diucapkan dalam sidang terbuka untuk umum oleh Hakim Ketua pada hari Rabu tanggal4 Februari 2015dengan susunan Majelis dan Panitera Pengganti sebagai berikut:<br>Drs. Tonggo Aritonang, Ak., M.Sc. Sebagai Hakim Ketua,<br>Drs. Didi Hardiman, Ak. Sebagai Hakim Anggota,<br>Djangkung Sudjarwadi, S.H., L.L.M. Sebagai Hakim Anggota,<br>Aditya Agung Priyo Nugroho Sebagai Panitera Pengganti,</p><p style='text-align:center'>dengan dihadiri oleh para Hakim Anggota, Panitera Pengganti, dihadiri oleh Terbanding serta dihadiri oleh Pemohon Banding.</p></div>";
        //$tes .= "<div style='height: 335px;' class='nocompare-content nocompare-content-pp' id='nocompare-wrapper-pp'><p class=\"head headtop\"><strong>Putusan Pengadilan Pajak Nomor : Put-60233/PP/M.XI.B/16/2015</strong></p><p style=\"text-align:center\"><strong>RISALAH</strong><br>Putusan Pengadilan Pajak Nomor : Put-60233/PP/M.XI.B/16/2015</p><p style=\"text-align:center\"><strong>JENIS PAJAK</strong><br>Pajak Pertambahan Nilai</p><p style=\"text-align:center\"><strong>TAHUN PAJAK</strong><br>2010</p><p style=\"text-align:center\"><strong>POKOK SENGKETA</strong><br>bahwa yang menjadi pokok sengketa adalah pengajuan gugatan terhadap koreksi Pajak Masukan yang dapat diperhitungkan Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 sebesar Rp536.050.458,00;</p><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">	<tbody>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p><strong>Menurut Terbanding</strong></p>			</div></td>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa dalam Pasal 26 ayat (3) UU KUP menyatakan “Keputusan Direktur Jenderal Pajak atas keberatan dapat berupa mengabulkan seluruhnya atau sebagian, menolak atau menambah besarnya jumlah pajak yang masih harus dibayar”. Hal ini secara jelas mengatur bahwa dalam keberatan, Terbanding dapat menambah jumlah pajak yang harus dibayar;</p>			</div></td>		</tr>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p style=\"text-align:justify\"><strong>Menurut Pemohon</strong></p>			</div></td>			<td style=\"text-align: justify; vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa Terbanding (Peneliti Keberatan) jelas-jelas telah melampaui kewenangannya didalam memutuskan permohonan keberatan Pemohon Banding karena tidak ada satu ketentuan pun yang mengatur dan yang memberikan wewenang kepada Terbanding (Peneliti Keberatan) untuk melakukan koreksi seperti yang telah dilakukan Terbanding kepada Pemohon Banding yaitu melakukan koreksi atas keberatan wajib pajak yang bukan obyek keberatan (sengketa). Pihak Terbanding khususnya Peneliti Keberatan tidak mempunyai dasar hukum untuk menambah dan melakukan koreksi atas faktur pajak masukan dari PT.Mitra Mandiri Serasi. Dalam hal ini Terbanding (Peneliti Keberatan) telah mengabaikan seluruh hasil pemeriksaan yang telah dilakukan oleh pejabat pemeriksa yang berwenang yaitu Tim Pemeriksa KPP Madya Tangerang yang merupakan wakil resmi Direktur Jenderal Pajak;</p>			</div></td>		</tr>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p style=\"text-align:justify\"><strong>Menurut Majelis</strong></p>			</div></td>			<td style=\"text-align: justify; vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa atas Surat Ketetapan Pajak Kurang Bayar Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011, Pemohon Banding mengajukan keberatan;</p>			<p style=\"text-align:justify\">bahwa didalam perhitungan Surat Ketetapan Pajak Kurang Bayar a quo, Pemohon Banding menyatakan pajak yang dapat diperhitungkan menurut Pemohon Banding adalah Rp9.382.447.030,00, sedangkan menurut Terbanding adalah Rp9.328.934.680,00, dengan demikian koreksi Terbanding adalah sebesar Rp 53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa dengan demikian pokok sengketa keberatan Pemohon Banding adalah koreksi Pajak Masukan yang menurut Pemohon Banding seharusnya dapat diperhitungkan adalah sebesar Rp53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa atas keberatan Pemohon Banding sebesar Rp 53.512.350,00 tersebut, Terbanding menerbitkan Keputusan Nomor KEP-306/WPJ.08/2013 tanggal 13 Februari 2013, dengan menambah koreksi Pajak Masukan sebesar Rp482.538.108,00 sehingga total koreksi Pajak Masukan menjadi Rp536.050.458,00;</p>			<p style=\"text-align:justify\">bahwa alasan Terbanding menambah koreksi karena berdasarkan penjelasan pada surat ND-91/WPJ.08/BD.04/2012 tanggal 13 Desember 2012, ZZZ NPWP 21.066.167.4-401.001 sedang dilakukan pemeriksaan Bukti Permulaan;</p>			<p style=\"text-align:justify\">bahwa berdasarkan peninjauan lapangan ke alamat ZZZ NPWP 21.066.167.4-401.001 yang dituangkan dalam Laporan Hasil Penelitian Lapangan Nomor LAP-232/WPJ.08/2013 tanggal 25 Januari 2013 sesuai dengan Surat Edaran Ddirektur Jenderal Pajak Nomor SE-132/PJ/2010 tanggal 30 November 2010 ZZZandiri Serasi diindikasikan sebagai penerbit faktur pajak tidak sah;</p>			<p style=\"text-align:justify\">bahwa Terbanding berpendapat keputusan Direktur Jenderal Pajak atas keberatan dapat menambah besarnya pajak yang harus dibayar sesuai dengan Pasal 26 ayat (4) Undang-Undang Nomor 6 Tahun 1983 tentang Ketentuan Umum dan Tata Cara Perpajakan sebagaimana telah beberapa kali diubah terakhir dengan Undang-Undang Nomor 16 Tahun 2009;</p>			<p style=\"text-align:justify\">bahwa atas Keputusan Nomor KEP-306/WPJ.08/2013 tanggal 13 Februari 2013 tersebut Pemohon Banding mengajukan banding dengan Surat Nomor 014/PNG-Pjk/IV/2013 tanggal 8 Mei 2013;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian di atas dan penjelasan beserta bukti yang disampaikan oleh Pemohon Banding dan Terbanding didalam persidangan, Majelis berpendapat sebagai berikut :</p>			<p style=\"text-align:justify\">bahwa atas Pajak Masukan sebesar Rp 9.328.934.680,00 telah diperiksa dan diakui oleh Terbanding yang dituangkan dalam Surat Ketetapan Pajak Kurang Bayar Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011;</p>			<p style=\"text-align:justify\">bahwa atas koreksi Pajak Masukan sebesar Rp 53.512.350,00 berdasarkan klarifikasi peneliti keberatan mendapat jawaban “ada” sebanyak 24 Faktur Pajak dengan nilai Rp 53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa atas dalil Terbanding tentang ZZZ diindikasikan sebagai penerbit faktur pajak tidak sah, sampai dengan sidang pemeriksaan dicukupkan tidak terdapat putusan Pengadilan yang telah mempunyai kekuatan hukum tetap, yang menyatakan ZZZ dipidana dibidang perpajakan atau tindak pidana lainnya yang dapat menimbulkan kerugian pada pendapatan negara, melainkan masih berstatus sebagai terperiksa “Bukti Permulaan”, oleh karena itu indikasi Pengusaha Kena Pajak penjual yaitu ZZZ sebagai penerbit faktur pajak fiktif tersebut tidak dapat dijadikan landasan hukum atas koreksi Terbanding;</p>			<p style=\"text-align:justify\">bahwa sesuai dengan Pasal 13 ayat (5) jo. Pasal 15 ayat (4) Undang-Undang Nomor 6 Tahun 1983 tentang Ketentuan Umum dan Tata Cara Perpajakan sebagaimana telah beberapa kali diubah terakhir dengan Undang-Undang Nomor 16 Tahun 2009, Terbanding dapat menerbitkan Surat Ketetapan Pajak atau Surat Ketetapan Pajak Kurang Bayar Tambahan terhadap ZZZ;</p>			<p style=\"text-align:justify\">bahwa Faktur Pajak a quo yang diterbitkan oleh ZZZ dapat menjadi “batal” dalam hal ZZZ terbukti menerbitkan faktur pajak tidak sah dan telah ada putusan pengadilan yang berkekuatan hukum tetap;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian di atas, Majelis berpendapat alasan koreksi Terbanding tidak memiliki alasan dan dasar hukum yang kuat, sehingga Majelis berkesimpulan tidak mempertahankan koreksi Terbanding sebesar Rp 536.050.458,00;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian tersebut di atas, rekapitulasi pendapat Majelis atas pokok sengketa adalah sebagai berikut :</p>			<div class=\"tablewrap\"><table align=\"center\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">				<tbody>					<tr>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>No</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Uraian Koreksi</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Total Sengketa&nbsp;(Rp)</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Tidak&nbsp;Dipertahankan&nbsp;(Rp)</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Dipertahankan&nbsp;(Rp)</strong></p>						</div></td>					</tr>					<tr>						<td><div class=\"wi\">						<p style=\"text-align:justify\">1</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\">Pajak Masukan</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">536.050.458,00</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">536.050.458,00</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">0,00</p>						</div></td>					</tr>				</tbody>			</table></div>			</div></td>		</tr>	</tbody></table><p style=\"text-align:center\"><strong>MENIMBANG</strong><br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai sanksi administrasi kecuali besarnya sanksi administrasi tergantung pada penyelesaian sengketa lainnya;</p><p style=\"text-align:center\">bahwa berdasarkan kesimpulan Majelis terhadap sengketa di atas, maka dengan kuasa Pasal 80 ayat (1) huruf b Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, Majelis memutuskan untuk mengabulkan sebagian banding Pemohon Banding, sehingga Pajak Masukan dihitung kembali sebagai berikut :<br>Jumlah Pajak Masukan menurut Terbanding sebesar Rp 8.846.396.572,00Jumlah Pajak Masukan Yang tidak dapat dipertahankan sebesar Rp 536.050.458,00Jumlah Pajak Masukan menurut Majelis sebesar Rp 9.382.447.030,00</p><p style=\"text-align:center\"><strong>MENGINGAT</strong><br>Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, dan ketentuan perundang-undangan lainnya serta peraturan hukum yang berlaku dan yang berkaitan dengan perkara ini;</p><p style=\"text-align:center\"><strong>MEMUTUSKAN<br>Menyatakan mengabulkan</strong> seluruhnya banding Pemohon Banding terhadap Keputusan Direktur Jenderal Pajak Nomor <strong>KEP-306/WPJ.08/2013 </strong><strong> </strong>tanggal <strong>13</strong><strong>Februari</strong><strong> </strong><strong>2013</strong><strong> </strong>tentang Keberatan Wajib Pajak atas Surat Ketetapan Pajak Kurang Bayar (SKPKB) Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011, atas nama <strong>XXX</strong>, sehingga dihitung kembali menjadi sebagai berikut :</p><table class=\"tablecontent\" style=\"line-height:1.6\" align=\"center\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">	<tbody>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:center\"><strong>Dasar Pengenaan Pajak :</strong></p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:center\">&nbsp;</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Ekspor</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Rp 205.899.833.380,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Penyerahan yang PPN-nya harus dipungut sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;6.166.426.760,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Penyerahan yang PPN-nya tidak dipungut</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;1.303.155.000,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Jumlah Dasar Pengenaan Pajak</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp 213.369.415.140,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Pajak Keluaran yang harus dipungut sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; 616.642.676,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Kredit Pajak :</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">&nbsp;</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Pajak Masukan yang dapat diperhitungkan</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;9.382.447.030,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Surat Tagihan Pajak (pokok kurang bayar)</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Dibayar dengan NPWP sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Jumlah pajak yang dapat diperhitungkan</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;9.382.447.030,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">PPN Kurang (Lebih) Bayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;8.765.804.354,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Kelebihan Pajak yg sudah dikompensasikan ke masa berikutnya</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;8.765.804.354,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Pajak Pertambahan Nilai yang kurang bayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Sanksi Administrasi : Pasal 13 (3) UU KUP</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">PPN yang masih harus/lebih dibayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>	</tbody></table><p style=\"text-align:center\">Demikian diputus di Jakarta pada hari Rabu, tanggal 5 Februari 2014 berdasarkan musyawarah Majelis XI B Pengadilan Pajak dengan susunan Majelis dan Panitera Pengganti sebagai berikut:<br>I Putu Setiawan sebagai Hakim Ketua,<br>I Made Sudana sebagai Hakim Anggota,<br>&nbsp;Arif Subekti sebagai Hakim Anggota,<br>Esti Cahya Inteni sebagai Panitera Pengganti,</p><p style=\"text-align:center\">Putusan Nomor Put.60233/PP/M.XI.B/16/2015 diucapkan dalam sidang terbuka untuk umum oleh Hakim Ketua pada hari Rabu tanggal 18 Maret 2015 dengan susunan Majelis dan Panitera Pengganti berdasarkan Penetapan Ketua Pengadilan Pajak Nomor Pen.008/PP/PM/III/Ucap/2015 tanggal 18 Maret 2015 sebagai berikut :<br>I Putu Setiawan sebagai Hakim Ketua,<br>Arif Subekti sebagai Hakim Anggota,<br>Masdi sebagai Hakim Anggota,<br>Esti Cahya Inteni sebagai Panitera Pengganti,</p><p style=\"text-align:center\">dengan dihadiri oleh para Hakim Anggota, Panitera Pengganti, serta tidak dihadiri oleh Terbanding dan tidak dihadiri oleh Pemohon Banding.</p></div>";
        $tes .= "</div>";
        $tes .= "</div>";
        $tes .= "<script src='". base_url() ."assets/themes/js/jquery.min.js'></script>";
        $tes .= "<script src='". base_url() ."assets/themes/js/html2canvas.js'></script>";
        $tes .= "<script src='". base_url() ."assets/themes/js/converttable.js'></script>";
        $tes .= "<script type=\"text/javascript\">";
        $tes .= "var html2pdf = {";
        //$tes .= "header: {";
        //$tes .= "height: \"1cm\",";
        //$tes .= "contents: '<div class=\"center\">page</div>'";
        //$tes .= "},";
        $tes .= "footer: {";
        $tes .= "height:\"1cm\",";
        $tes .= "contents: '<div style=\"height:0.2cm;\"></div><div style=\"border-top:1px solid #CCC;padding-top:0;margin-top:0;height:0.7cm;text-align:center;background:url(http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg) no-repeat bottom center;background-size:71px 20px;\"></div>'";
        $tes .= "}";
        $tes .= "};";
        $tes .= "</script>";
        $tes .= "</body></html>";
		//echo $tes;
		
		
            $dir = 'newdir';
            if ( !file_exists($dir) ) {
                    $oldmask = umask(0);  // helpful when used in linux server  
                    mkdir ($dir, 0777);
            } else{                
                //chmod("newdir", 0777);
            }
            
            $rawfilename = mt_rand();
            $filename = 'newdir/'.mt_rand() . '.html';
            file_put_contents($filename, $tes);
            chmod($filename, 0777);
            $cmd = '/usr/bin/xvfb-run --server-args="-screen 0, 1024x768x24" /usr/local/bin/wkhtmltopdf --no-outline --margin-top 10 --margin-right 10 --margin-bottom 15 --margin-left 10 --disable-smart-shrinking '.$filename.' --encoding utf-8 --footer-html newdir/assets/footer.html newdir/'.$rawfilename.'.pdf 2>&1';
            exec($cmd, $var, $res);

            //echo (print_r(array_values($var)));
            echo $rawfilename;
	}
	
	public function cetaksonline2()
	{
		if($this->user_auth->is_logged_in())
		{
			$id = $this->input->get('id');
			$type = $this->input->get('type');
			if($id && $type) {
				if($type == 'pp') {
					$pp = $this->putusan_pengadilan_model->get($id);
					$pattern = "#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#"; 
					$pp_full = '<p class="head headtop"><strong>Putusan Pengadilan Pajak Nomor : '.$pp['name'].'</strong></p>';
					$pp_full .= $pp['isi_putusan'];
					
					$tes = "<html><head><style>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700,600); table tr{page-break-inside:avoid;} body,p, table tr td { font-family: 'Open Sans'; font-size:10px !important;line-height:12px !important; } .nocompare-content-pp {height:auto !important;} table img {margin:0 auto !important;display:block;padding:0 !important;max-width:100% !important;height:auto !important;} html, body {height:auto !important;} .tablewrap {padding:0 !important;width:100% !important;display:block !important;overflow:inherit !important;} .tablewrap .tablewrap {padding:0 !important;margin:0 !important;} .nocompare-content {overflow-x:auto !important;} .doc-modal-pp table table .wi{padding:0 !important;}</style>";
					$tes .= "<link href='". base_url() ."assets/themes/css/custom.css?v=5' rel='stylesheet' type='text/css'>";
					$tes .= "</head><body>";
					$tes .= "<div class='doc-modal-pp'>";
					$tes .= "<div class='modal-desc' id='modal-contents-pp' style='page-break-before: always;'>";
					$tes .= "<img src='http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg' width='0' height='0' />";
					$tes .= "<div style='height: 335px;' class='nocompare-content nocompare-content-pp' id='nocompare-wrapper-pp'>";
					$tes .= $pp_full;
					$tes .= "</div>";
					$tes .= "</div>";
					$tes .= "</div>";
					$tes .= "<script src='". base_url() ."assets/themes/js/jquery.min.js'></script>";
					$tes .= "<script src='". base_url() ."assets/themes/js/html2canvas.js'></script>";
					$tes .= "<script src='". base_url() ."assets/themes/js/converttable.js'></script>";
					$tes .= "<script type=\"text/javascript\">";
					$tes .= "var html2pdf = {";
					$tes .= "footer: {";
					$tes .= "height:\"1.6cm\",";
					$tes .= "contents: '<div style=\"border-top:1px solid #CCC;padding-top:0.2cm;margin-top:0.2cm;height:40px;text-align:center;background:url(http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg) no-repeat center center;\"></div>'";
					$tes .= "}";
					$tes .= "};";
					$tes .= "</script>";
					$tes .= "</body></html>";
					
					echo $tes;
				} else if($type == 'rp') {
					$pj = $this->regulasi_pajak_model->get($id);
					$pj_view = $pj['view'];

					$pj_view_new = (int)$pj_view+1;

					$data = array('view' => $pj_view_new);
					$this->regulasi_pajak_model->update($id, $data);
					
					$regulasi_pajak = $this->regulasi_pajak_model->get($id);

					$id_o = $regulasi_pajak['id_o'];
					$body_final = $regulasi_pajak['body_final'];

					if(!$id_o || $id_o == NULL || $id_o == 0)
					{
						$linklist  = $regulasi_pajak['linklist'];

						if($linklist != '') 
						{
							$body_replace = regulasi_ortax_format_body_rp($linklist, $body_final);
						} 
						else
						{
							$body_replace = $body_final;
						}
					}
					else
					{
						$linklist = get_linklist($id_o);

						if($linklist != '') 
						{
							$body_replace = regulasi_ortax_format_body($linklist, $body_final);
						} 
						else
						{
							$body_replace = $body_final;
						}
					}

					//echo $body_replace.'<div class="footerdoc"><img src="'.site_url().'assets/themes/images/docfooter.jpg"></div>';
					
					$id = $this->input->post('id');
					$regulasi_pajak = $this->regulasi_pajak_model->get($id);
					
					$tes = "<html><head><style>@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700,600); table tr{page-break-inside:avoid;} body,p, table tr td { font-family: 'Open Sans'; font-size:10px !important;line-height:12px !important; } .nocompare-content-pp {height:auto !important;} table img {margin:0 auto !important;display:block;padding:0 !important;max-width:100% !important;height:auto !important;} html, body {height:auto !important;} .tablewrap {padding:0 !important;width:100% !important;display:block !important;overflow:inherit !important;} .tablewrap .tablewrap {padding:0 !important;margin:0 !important;} .nocompare-content {overflow-x:auto !important;} .doc-modal-pp table table .wi{padding:0 !important;}</style>";
					$tes .= "<link href='". base_url() ."assets/themes/css/custom.css?v=5' rel='stylesheet' type='text/css'>";
					$tes .= "</head><body>";
					$tes .= "<div class='doc-modal-pp'>";
					$tes .= "<div class='modal-desc' id='modal-contents-pp' style='page-break-before: always;'>";
					$tes .= "<img src='http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg' width='0' height='0' />";
					//$tes .= "<div style='height: 335px;' class='nocompare-content nocompare-content-pp' id='nocompare-wrapper-pp'><p class='head headtop'><strong>Putusan Pengadilan Pajak Nomor : Put-59334/PP/M.XVB/16/2015</strong></p><p style='text-align:center'><strong>RISALAH</strong><br>Putusan Pengadilan Pajak Nomor : Put-59334/PP/M.XVB/16/2015</p><p style='text-align:center'><strong>JENIS PAJAK</strong><br>Pajak Pertambahan Nilai</p><p style='text-align:center'><strong>TAHUN PAJAK</strong><br>2008</p><p style='text-align:center'><strong>POKOK SENGKETA</strong><br>bahwa yang menjadi pokok sengketa adalah pengajuan banding terhadapkoreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00);</p><table align='left' border='0' cellpadding='0' cellspacing='0'>	<tbody>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Terbanding</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa PPN yang dibayar oleh Pemohon Banding atas pembayaran apa yang disebut Pemohon Banding sebagai royalti dan ERP karena tidak terbukti sebagai pembayaran yang mempunyai hubungan Iangsung dengan usaha;</p>			<p>bahwa sehubungan dengan uraian penelitian atas DPP Pemanfaatan BKP Tidak Berwujud di atas, maka dapat disimpulkan bahwa terdapat koreksi atas DPP Pemanfaatan BKP Tidak Berwujud dari Luar Daerah Pabean, oleh karena itu atas pembayaran PPN sejumlah Rp4.919.183.561,00 yang menjadi pokok sengketa pada penelitian ini adalah bukan pembayaran PPN atas pemanfaatan BKP Tidak Berwujud dari Luar Daerah Pabean sehingga tidak dapat dikreditkan sebagai Pajak Masukan;</p>			</div></td>		</tr>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Pemohon</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa Pemohon Banding <strong> </strong><strong>tidak</strong><strong> </strong><strong>setuju</strong><strong> </strong>dengan koreksi yang dilakukan Terbanding dan <strong> </strong><strong>mengajukan</strong><strong> </strong><strong>banding</strong><strong> </strong>atas koreksi PPN yang dapat diperhitungkan sehubungan dengan Pembayaran PPN atas Pemanfaatan JKP dan/atau BKP Tidak Berwujud dari luar daerah pabean berupa Intercompany Technical Assistance dan Enterprise Resource Planning (ERP) Platform sebesar Rp 4.919.183.561,00;</p>			<p>bahwa Pemohon Banding nyata-nyata telah melakukan pembayaran PPN dan melaporkan dalam SPT Masa PPN atas pemanfaatan JKP dan/atau BKP Tidak Berwujud dari luar daerah pabean;</p>			</div></td>		</tr>		<tr>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p><strong>Menurut Majelis</strong></p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>:</p>			</div></td>			<td style='text-align: justify; vertical-align: top;'><div class='wi'>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00)karena merupakan konsistensi dari adanya koreksi pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) Fee pada sengketa koreksi Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa menurut pendapat Majelis, Terbanding menetapkan pembayaran objek sebesar Rp49.191.835.605,00 bukan merupakan pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP), melainkan pembayaran dividen kepada pemegang saham sehingga bukan merupakan objek PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean;</p>			<p>bahwa oleh karena koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean bersumber dari koreksi positif atas kedua biaya tersebut di atas, maka pertimbangan dan kesimpulan Majelis terhadap sengketa ini mengikuti hasil pemeriksaan Majelis terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) pada sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008 berupa koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) telah diputus oleh Pengadilan Pajak dengan Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015;</p>			<p>bahwa hasil pemeriksaan, pertimbangan dan kesimpulan Majelis terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP)sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015 adalah sebagai berikut :<br>			koreksi positif Intercompany Technical Assistance Fee sebesar USD5,349,708.00</p>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi positif <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00karena perhitungan biaya Intercompany Technical Assistance Fee Pemohon Banding berdasarkan kepada Operating Income dan Third Party Revenue, bukan atas dasar kekayaan intelektual tertentu yang digunakan oleh Pemohon Banding;bahwa menurut Terbanding, metode perhitungan Pemohon Banding tidak lazim karena dengan metode ini dapat terjadi tidak ada <em>Technical Assistance Fee </em>yang harus dikeluarkan padahal seharusnya apabila telah diketahui terjadi penggunaan kekayaan intelektual maka akan timbul biaya yang harus dibebankan oleh Pemohon Banding yang erat hubungannya dalam rangka biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana disebutkan dalam Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Terbanding ketidaklaziman biaya <em>Intercompany Technical Assistance Fee </em>yang dibayarkan kepada Halliburton Energy Services, Inc ini pada prinsipnya adalah merupakan pembagian laba (Dividen) sesuai dengan Pasal 4 ayat (1) huruf g Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Terbanding, biaya <em>Intercompany Technical Assistance Fee</em>sebesar USD5,349,708.00 dikoreksi positif oleh Terbanding sesuai Pasal 9 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding tidak mempunyai resources untuk melakukan Research &amp; Development, dan tidak pernah menemukan sendiri baik itu teknologi, formula, maupun metode yang digunakan dalam proses jasa di bidang minyak dan gas bumi;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding tidak mungkin dapat melakukan penyerahan jasa tanpa menggunakan teknologi, formula, maupun metode yang hak patennya dimiliki oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut Pemohon Banding, biaya <em>Technical Assistance Fee </em>merupakan biaya yang dapat dikurangkan sesuai dengan Pasal 6 ayat (1) huruf a Undang-Undang Pajak Penghasilan, sedangkan pembayaran atas Technical Assistance Fee telah dilakukan melalui intercompany settlement;</p>			<p>bahwa menurut pendapat Majelis, Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan pengurangan serta menentukan utang sebagai modal untuk menghitung besarnya Penghasilan Kena Pajak bagi Wajib Pajak yang mempunyai hubungan istimewa dengan Wajib Pajak lainnya sesuai dengan kewajaran dan kelaziman usaha yang tidak dipengaruhi oleh hubungan istimewa</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>maksud diadakannya ketentuan ini adalah untuk mencegah terjadinya penghindaran pajak, yang dapat terjadi karena adanya hubungan istimewa. Apabila terdapat hubungan istimewa, kemungkinan dapat terjadi penghasilan dilaporkan kurang dari semestinya ataupun pembebanan biaya melebihi dari yang seharusnya. Dalam hal demikian Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan atau biaya sesuai dengan keadaan seandainya di antara para Wajib Pajak tersebut tidak terdapat hubungan istimewa. Dalam menentukan kembali jumlah penghasilan dan atau biaya tersebut dapat dipakai beberapa pendekatan, misalnya data pembanding, alokasi laba berdasar fungsi atau peran serta dari Wajib Pajak yang mempunyai hubungan istimewa dan</em><em> </em><em>indikasi</em><em> </em><em>serta</em><em> </em><em>data</em><em> </em><em>lainnya.</em><em> </em><em>Demikian</em><em> </em><em>pula</em><em> </em><em>kemungkinan</em><em> </em><em>terdapat</em><em> </em><em>penyertaan</em><em> </em><em>modal</em><em> </em><em>secara terselubung, dengan menyatakan penyertaan modal tersebut sebagai utang, maka Direktur Jenderal Pajak berwenang untuk menentukan utang tersebut sebagai modal perusahaan. Penentuan tersebut dapat dilakukan misalnya melalui indikasi mengenai perbandingan antara modal dengan utang yang lazim terjadi antara para pihak yang tidak dipengaruhi oleh hubungan istimewa atau berdasar data atau indikasi lainnya. Dengan demikian bunga yang dibayarkan sehubungan dengan utang yang dianggap sebagai penyertaan modal itu tidak diperbolehkan untuk dikurangkan, sedangkan bagi pemegang saham yang menerima atau memperolehnya dianggap sebagai dividen yang dikenakan pajak.</em>”</p>			<p>bahwa Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 menyatakan :<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>sebagaimana</em><em> </em><em>dimaksud</em><em> </em><em>dalam</em><em> </em><em>ayat</em><em> </em><em>(3)</em><em> </em><em>dan</em><em> </em><em>(3a),</em><em> </em><em>Pasal</em><em> </em><em>8</em><em> </em><em>ayat</em><em> </em><em>(4),</em><em> </em><em>Pasal</em><em> </em><em>9</em><em> </em><em>ayat (1) huruf f, dan Pasal 10 ayat (1) dianggap ada apabila :<br>			a. <em> </em>Wajib Pajak mempunyai penyertaan modal langsung atau tidak langsung paling rendah 25% (dua puluh lima persen) pada Wajib Pajak lain, atau hubungan antara Wajib Pajak dengan penyertaan paling rendah 25% (dua puluh lima persen) pada dua Wajib Pajak atau lebih, demikian pula hubungan antara dua Wajib Pajak atau lebih yang disebut terakhir; atau<br>			b. Wajib<em> </em>Pajak<em> </em>menguasai</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>lainnya</em><em> </em><em>atau</em><em> </em><em>dua</em><em> </em><em>atau</em><em> </em><em>lebih</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>berada</em><em> </em><em>di</em><em> </em><em>bawah penguasaan yang sama baik langsung maupun tidak langsung; atau</em><br>			c. <em>terdapat hubungan keluarga baik sedarah maupun semenda dalam garis keturunan lurus dan atau ke samping satu derajat;</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan:<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>di</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>ketergantungan</em><em> </em><em>atau</em><em> </em><em>keterikatan satu dengan yang lain yang disebabkan karena :<br>			a. <em> </em>kepemilikan atau penyertaan modal;b. </em><em> </em><em>adanya penguasaan melalui manajemen atau penggunaan teknologi.<br>			Selain karena hal-hal tersebut di atas, hubungan istimewa di antara Wajib Pajak orang pribadi dapat pula terjadi karena adanya hubungan darah atau karena perkawinan;</em></p>			<p><em>Huruf a<br>			Hubungan istimewa dianggap ada apabila terdapat hubungan kepemilikan yang berupa penyertaan modal sebesar 25% (dua puluh lima persen) atau lebih secara langsung ataupun tidak langsung. Misalnya, PT A mempunyai 50% (lima puluh persen) saham PT B. Pemilikan saham oleh PT A merupakan penyertaan langsung. Selanjutnya apabila PT B tersebut mempunyai 50% (lima puluh persen) saham PT C, maka PT A sebagai pemegang saham PT B secara tidak langsung mempunyai penyertaan pada PT C sebesar 25% (dua puluh lima persen). Dalam hal demikian antara PT A, PT B dan PT C dianggap terdapat hubungan istimewa. Apabila PT A juga memiliki 25% (dua puluh lima persen) saham PT D, maka antara PT B, PT C dan PT D dianggap terdapat hubungan istimewa. Hubungan kepemilikan seperti tersebut di atas dapat juga terjadi antara orang pribadi dan badan;</em></p>			<p><em>Huruf b</em><br>			<em>Hubungan<em> </em>istimewa</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>juga</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>penguasaan</em><em> </em><em>melalui</em><em> </em><em>manajemen atau penggunaan teknologi, walaupun tidak terdapat hubungan kepemilikan. Hubungan istimewa dianggap ada apabila satu atau lebih perusahaan berada di bawah penguasaan yang sama. Demikian juga hubungan antara beberapa perusahaan yang berada dalam penguasaan yang sama tersebut.</em></p>			<p><em>Huruf c<br>			Yang dimaksud dengan hubungan keluarga sedarah dalam garis keturunan lurus satu derajat adalah ayah, ibu, dan anak, sedangkan hubungan keluarga sedarah dalam garis keturunan ke samping satu derajat adalah saudara. Yang dimaksud dengan keluarga semenda dalam garis keturunan lurus satu derajat adalah mertua dan anak tiri, sedangkan hubungan keluarga semenda dalam garis keturunan ke samping satu derajat adalah ipar;</em>”</p>			<p>bahwa menurut pendapat Majelis,makna Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 adalah jika terdapat hubungan istimewa maka ada kemungkinan terjadi penghindaran pajak melalui :<br>			a. Transaksi yang tidak wajar,b. Transaksi wajar tetapi nilainya tidak wajar;</p>			<p>bahwa semakin tinggi level hubungan istimewa maka semakin tinggi kemungkinan terdapat kedua macam transaksi tersebut diatas;</p>			<p>bahwa berdasarkan Penjelasan Tertulis Pemohon Banding tanggal 5 Februari 2013, tanpa nomor, diketahui bahwa Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa skema Pemegang saham Pemohon Banding adalah sebagai berikut:</p>			<p style='text-align:center'><img alt='' src='data:image/png;base64,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' style='height:215px; width:591px'></p>			<p>bahwa dengan demikian Majelis berkesimpulan bahwa Pemohon Banding dan Halliburton Energy Services Inc. mempunyai hubungan istimewa dan tidak dapat disebut sebagai pihak-pihak yang independen;</p>			<p>bahwa berdasarkan <em>Amanded and Restated Tech Fee Agreement</em>(P.5) Pemohon Banding dapat memanfaatkan dan menggunakan seluruh <em>patented and non-patented technology, software, technical and non-technical trade secrets and know-how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Majelis, dalam halpemilik dan pengguna teknologi adalah pihak-pihak independen (tidak ada hubungan istimewa), maka pengguna teknologi mau membayar <em>Technical Assistance Fee </em>kepada pemilik teknologi karena pengguna teknologi mengharapkan keuntungan (profit) dari penjualan jasa yang menggunakan teknologi tersebut;</p>			<p>bahwa dengan kata lain, pengguna teknologi tidak akan menjual jasa yang menurut perhitungannya penjualan jasa tersebut tidak dapat memberikan keuntungan;</p>			<p>bahwa menurut pendapat Majelis, pertimbangan utama perhitungan besarnya <em>Technical</em><em> </em><em>Assistance Fee </em>yang dibayarkan pengguna teknologi didasarkan seberapa besar keuntungan yang diharapkannya dari penjualan jasa pihak pengguna teknologi tersebut;</p>			<p>bahwa setelah mengetahui keuntungan yang diharapkan, kemudian Pemohon Banding menghitung besaran <em>Technical Assistance Fee </em>yang pantas untuk dibayarkan;</p>			<p>bahwa besaran besarnya <em>Technical Assistance Fee </em>yang pantas dibayarkan tersebut dapat dituangkan dalam bentuk hitungan sekian persen dari peredaran usaha atau sekian persen dari nilai produksi atau sekian persen dari keuntungan, atau sejumlah tertentu dan sebagainya;</p>			<p>bahwa setelah pembayaran besarnya <em>Technical Assistance Fee </em>tentu masih ada keuntungan yang diharapkan untuk dibagikan kepada pemilik/pemegang saham;</p>			<p>bahwa sampai dengan persidangan selesai, Pemohon Banding tidak pernah memberikan data estimasi/proyeksi keuntungan yang akan diperoleh Pemohon Banding dalam melakukan kegiatan usahanya, sehingga Majelis berpendapat besaran pembayaran besarnya <em>Technical Assistance Fee </em>tersebut tidak dapat dinilai kewajarannya;</p>			<p>bahwa berdasarkan Analisa atas Laporan Keuangan untuk Perpajakan yang diserahkan Pemohon Banding dalam persidangan terdapat fakta Penghasilan Neto Komersial Pemohon Banding sebagai berikut :</p>			<div class='tablewrap'><table align='center' border='1' cellpadding='0' cellspacing='0'>				<tbody>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>Tahun</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2002</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2003</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2004</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2005</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2006</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2007</strong></div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: center;'><strong>2008</strong></div>						</div></td>					</tr>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div>Net Income&nbsp;before Tax&nbsp;(USD)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div style='text-align: right;'>(4,900,700.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(3,673,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(7,419,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(2,903.000,00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(1,053,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>5,774,000.00</div>						</div></td>						<td style='text-align: justify; vertical-align: top; width: 5px; white-space: nowrap;'><div class='wi'>						<div style='text-align: right;'>(3,148,000.00)</div>						</div></td>					</tr>				</tbody>			</table></div>			<p>bahwa berdasarkan fakta dan data tersebut, Pemohon Banding hanya mengalami laba secara komersial pada Tahun 2007 sebesar USD 5,774,000.00, sementara Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 Pemohon Banding mengalami kerugian secara komersial;</p>			<p>bahwa akumulasi kerugian komersial (accumulated deficit) Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 berjumlah USD 23,096,700.00;</p>			<p>bahwa dalam kondisi demikian menurut pendapat Majelis,pembayaran<em>Intercompany Technical Assistance Fee</em>secara terus-menerus setiap tahun yang dilakukan oleh Pemohon Banding kepada Halliburton Energy Services Inc. yang merupakan pihak yang memiliki hubungan istimewa adalah sesuatu yang tidak wajar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan-pertimbangan Majelis sebagaimana tersebut di atas, Majelisberkesimpulan bahwa koreksi Terbanding atas <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00tetap dipertahankan;</p>			<p>koreksi positif biaya Enterprise Resource Planning (ERP) sebesar USD791,784.00</p>			<p>bahwa menurut pendapat Majelis,Terbanding melakukan koreksi positif biaya Enterprise Resource Planning (ERP) sebesar USD791,784.00 karena:</p>			<p style='margin-left:40px'>- dibebankan oleh Pemohon Banding berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services, Inc, USA (HES, Inc) yang berlaku efektif 01 Januari 2002;<br>			-biaya Enterprise Resource Planning (ERP) Fee (acc.610302) yang dibebankan oleh Pemohon Banding adalah atas penggunaan software platform standard berbasis SAP Integrated ERP yang dapat diakses/dimanfaatkan/digunakan untuk mendukung operasional perusahaan;<br>			-perhitungan biaya Enterprise Resource Planning (ERP) Fee sesuai dengan article V Global ERP Platform Agreement dengan menggunakan dua metode perhitungan mana yang lebih rendah yaitu antara Calculation of Annual Cap dengan Calculation of Provisional Fee;<br>			-berdasarkan dua metode ini, perhitungan biaya Enterprise Resource Planning (ERP) Fee didasarkan kepada Operating Income dan Third Party Revenue bukan atas dasar nilai fee tertentu yang telah disepakati oleh kedua pihak yang berjanji atas penggunaan software demikian juga metode penghitungan ini tidak berkaitan langsung dengan ada dan tidaknya upgrading, maintenance dan service yang diberikan untuk software yang bersangkutan sehingga tidak dapat diyakini pengeluaran biaya Enterprise Resource Planning (ERP) Fee dalam rangka biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana disebutkan dalam Pasal 6 ayat (1) huruf a Undang-Undang Nomor 17 Tahun 2000;<br>			-bahwa ketidaklaziman atas biaya Enterprise Resource Planning (ERP) Fee yang dibayarkan kepada HES,Inc. ini pada prinsipnya adalah merupakan pembagian laba (dividen) sesuai dengan Pasal 4 ayat (1) huruf g Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa menurut Pemohon Banding, fakta dan bukti kebenaran adanya biaya Enterprise Resource Planning Fee tersebut telah Pemohon Banding berikan kepada Terbanding selama pemeriksaan termasuk kontrak dengan vendor, invoice vendor, invoice dan pembukuan, kontrak dengan customer, invoice penghasilan kepada customer, pembayaran PPN atas pemanfaatan BKP/JKP tidak berwujud dari luar daerah pabean, pembayaran PPh Pasal 26 terkait, dan juga Audit Report dan Transfer Pricing Study;</p>			<p>bahwa menurut Pemohon Banding, wewenang berdasarkan Pasal 18 ayat (3) Undang-UndangNomor7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor17 Tahun 2000 yang dijadikan dasar hukum Terbanding dalam melakukan koreksi, tidak dapat digunakan Terbanding untuk meniadakan biaya tersebut;</p>			<p>bahwa menurut Pemohon Banding, koreksi Terbanding dalam hal ini melanggar Pasal 18 ayat (3) Undang-UndangNomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 karena justru koreksi tersebut menjadi tidak wajar karena mengabaikan transaksi yang terjadi dan tidak dapat membuktikan mengenai kewajaran peniadaan biaya tersebut;</p>			<p>bahwa menurut Pemohon Banding, dengan adanya kontrak dengan customer, invoice kepada customer, pengelolaan inventory dan seluruh resources perusahaan, SPT Pajak Penghasilan, SPT Pajak Pertambahan Nilai merupakan bukti adanya manfaat atas biaya Enterprise Resource Planning tersebut;bahwa menurut pendapat Majelis, Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :</p>			<p>“<em>Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan pengurangan serta menentukan utang sebagai modal untuk menghitung besarnya Penghasilan Kena Pajak bagi Wajib Pajak yang mempunyai hubungan istimewa dengan Wajib Pajak lainnya sesuai dengan kewajaran dan kelaziman usaha yang tidak dipengaruhi oleh hubungan istimewa</em>”</p>			<p>&nbsp;</p>			<p>bahwa Penjelasan Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan :<br>			“<em>maksud diadakannya ketentuan ini adalah untuk mencegah terjadinya penghindaran pajak, yang dapat terjadi karena adanya hubungan istimewa. Apabila terdapat hubungan istimewa, kemungkinan dapat terjadi penghasilan dilaporkan kurang dari semestinya ataupun pembebanan biaya melebihi dari yang seharusnya. Dalam hal demikian Direktur Jenderal Pajak berwenang untuk menentukan kembali besarnya penghasilan dan atau biaya sesuai dengan keadaan seandainya di antara para Wajib Pajak tersebut tidak terdapat hubungan istimewa. Dalam menentukan kembali jumlah penghasilan dan atau biaya tersebut dapat dipakai beberapa pendekatan, misalnya data pembanding, alokasi laba berdasar fungsi atau peran serta dari Wajib Pajak yang mempunyai hubungan istimewa dan indikasi serta data lainnya. Demikian pula kemungkinan terdapat penyertaan modal secara terselubung, dengan menyatakan penyertaan modal tersebut sebagai utang, maka Direktur Jenderal Pajak berwenang untuk menentukan utang tersebut sebagai modal perusahaan. Penentuan tersebut dapat dilakukan misalnya melalui indikasi mengenai perbandingan antara modal dengan utang yang lazim terjadi antara para pihak yang tidak dipengaruhi oleh hubungan istimewa atau berdasar data atau indikasi lainnya. Dengan demikian bunga yang dibayarkan sehubungan dengan utang yang dianggap sebagai penyertaan modal itu tidak diperbolehkan untuk dikurangkan, sedangkan bagi pemegang saham yang menerima atau memperolehnya dianggap sebagai dividen yang dikenakan pajak.</em>”</p>			<p>bahwa Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 menyatakan :<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>sebagaimana</em><em> </em><em>dimaksud</em><em> </em><em>dalam</em><em> </em><em>ayat</em><em> </em><em>(3)</em><em> </em><em>dan</em><em> </em><em>(3a),</em><em> </em><em>Pasal</em><em> </em><em>8</em><em> </em><em>ayat</em><em> </em><em>(4),</em><em> </em><em>Pasal</em><em> </em><em>9</em><em> </em><em>ayat (1) huruf f, dan Pasal 10 ayat (1) dianggap ada apabila :<br>			a. <em> </em>Wajib Pajak mempunyai penyertaan modal langsung atau tidak langsung paling rendah 25% (dua puluh lima persen) pada Wajib Pajak lain, atau hubungan antara Wajib Pajak dengan penyertaan paling rendah 25% (dua puluh lima persen) pada dua Wajib Pajak atau lebih, demikian pula hubungan antara dua Wajib Pajak atau lebih yang disebut terakhir; atau<br>			b. Wajib<em> </em>Pajak<em> </em>menguasai</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>lainnya</em><em> </em><em>atau</em><em> </em><em>dua</em><em> </em><em>atau</em><em> </em><em>lebih</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>berada</em><em> </em><em>di</em><em> </em><em>bawah penguasaan yang sama baik langsung maupun tidak langsung; atau</em><br>			c. <em>terdapat hubungan keluarga baik sedarah maupun semenda dalam garis keturunan lurus dan atau ke samping satu derajat;</em>”</p>			<p>bahwa Penjelasan Pasal 18 ayat (4) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 menyatakan:<br>			“<em>Hubungan</em><em> </em><em>istimewa</em><em> </em><em>di</em><em> </em><em>antara</em><em> </em><em>Wajib</em><em> </em><em>Pajak</em><em> </em><em>dapat</em><em> </em><em>terjadi</em><em> </em><em>karena</em><em> </em><em>ketergantungan</em><em> </em><em>atau</em><em> </em><em>keterikatan</em><em>satu dengan yang lain yang disebabkan karena :<br>			a. <em> </em>kepemilikan atau penyertaan modal;b. </em><em> </em><em>adanya penguasaan melalui manajemen atau penggunaan teknologi.<br>			Selain karena hal-hal tersebut di atas, hubungan istimewa di antara Wajib Pajak orang pribadi dapat pula terjadi karena adanya hubungan darah atau karena perkawinan;</em></p>			<p><em>Huruf a<br>			Hubungan istimewa dianggap ada apabila terdapat hubungan kepemilikan yang berupa penyertaan modal sebesar 25% (dua puluh lima persen) atau lebih secara langsung ataupun tidak langsung. Misalnya, PT A mempunyai 50% (lima puluh persen) saham PT B. Pemilikan saham oleh PT A merupakan penyertaan langsung. Selanjutnya apabila PT B tersebut mempunyai 50% (lima puluh persen) saham PT C, maka PT A sebagai pemegang saham PT B secara tidak langsung mempunyai penyertaan pada PT C sebesar 25% (dua puluh lima persen). Dalam hal demikian antara PT A, PT B dan PT C dianggap terdapat hubungan istimewa. Apabila PT A juga memiliki 25% (dua puluh lima persen) saham PT D, maka antara PT B, PT C dan PT D dianggap terdapat hubungan istimewa. Hubungan kepemilikan seperti tersebut di atas dapat juga terjadi antara orang pribadi dan badan;</em></p>			<p><em>Huruf b</em><br>			<em>Hubungan istimewa antara Wajib Pajak dapat juga terjadi karena penguasaan melalui manajemen atau penggunaan teknologi, walaupun tidak terdapat hubungan kepemilikan. Hubungan istimewa dianggap ada apabila satu atau lebih perusahaan berada di bawah penguasaan yang sama. Demikian juga hubungan antara beberapa perusahaan yang berada dalam penguasaan yang sama tersebut.</em></p>			<p><em>Huruf c</em><br>			<em>Yang dimaksud dengan hubungan keluarga sedarah dalam garis keturunan lurus satu derajat adalah ayah, ibu, dan anak, sedangkan hubungan keluarga sedarah dalam garis keturunan ke samping satu derajat adalah saudara. Yang dimaksud dengan keluarga semenda dalam garis keturunan lurus satu derajat adalah mertua dan anak tiri, sedangkan hubungan keluarga semenda dalam garis keturunan ke samping satu derajat adalah ipar;</em>”</p>			<p>bahwa menurut pendapat Majelis,makna Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 adalah jika terdapat hubungan istimewa maka ada kemungkinan terjadi penghindaran pajak melalui :<br>			a. Transaksi yang tidak wajar,b. Transaksi wajar tetapi nilainya tidak wajar;</p>			<p>bahwa semakin tinggi level hubungan istimewa maka semakin tinggi kemungkinan terdapat kedua macam transaksi tersebut diatas;</p>			<p>bahwa berdasarkan Penjelasan Tertulis Pemohon Banding tanggal 5 Februari 2013, tanpa nomor, diketahui bahwa Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;bahwa skema Pemegang saham Pemohon Banding adalah sebagai berikut :<img alt='' src='data:image/png;base64,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' style='height:208px; width:636px'></p>			<p>bahwa dengan demikian Majelis berkesimpulan bahwa Pemohon Banding dan Halliburton Energy Services Inc. mempunyai hubungan istimewa dan tidak dapat disebut sebagai pihak-pihak yang independen;</p>			<p>bahwa biaya Enterprise Resource Planning (ERP) dibayar oleh Pemohon Banding kepada Halliburton Energy Services Inc. berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services Inc.;</p>			<p>bahwa Pemohon Banding membayar biaya Enterprise Resource Planning (ERP) kepada Halliburton Energy Services Inc. atas penggunaan <em>software </em>yang dikembangkan oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Majelis, dalam halpemilik dan pengguna Teknologi (<em>software) </em>adalah pihak- pihak independen (tidak ada hubungan istimewa), maka pengguna teknologi mau membayar biaya Enterprise Resource Planning (ERP)kepada pemilik teknologi karena pengguna teknologi mengharapkan keuntungan (profit) dari penjualan jasa yang menggunakan teknologi tersebut;</p>			<p>bahwa dengan kata lain, pengguna teknologi tidak akan menjual jasa yang menurut perhitungannya penjualan jasa tersebut tidak dapat memberikan keuntungan;</p>			<p>bahwa menurut pendapat Majelis, pertimbangan utama perhitungan besarnya biaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan pengguna teknologi didasarkan seberapa besar keuntungan yang diharapkannya dari penjualan jasa pihak pengguna teknologi tersebut;</p>			<p>bahwa setelah mengetahui keuntungan yang diharapkan, kemudian Pemohon Banding menghitung besaran <em>Enterprise Resource Planning (ERP)</em>yang pantas untuk dibayarkan;</p>			<p>bahwa besaran besarnya biaya <em>Enterprise Resource Planning (ERP)</em>yang pantas dibayarkan tersebut dapat dituangkan dalam bentuk hitungan sekian persen dari peredaran usaha atau sekian persen dari nilai produksi atau sekian persen dari keuntungan, atau sejumlah tertentu dan sebagainya;</p>			<p>bahwa setelah pembayaran besarnya biaya <em>Enterprise Resource Planning (ERP) </em>tentu masih ada keuntungan yang diharapkan untuk dibagikan kepada pemilik/pemegang saham;</p>			<p>bahwa sampai dengan persidangan selesai, Pemohon Banding tidak pernah memberikan data estimasi/proyeksi keuntungan yang akan diperoleh Pemohon Banding dalam melakukan kegiatan usahanya, sehingga Majelis berpendapat besaran pembayaran besarnya biaya <em>Enterprise Resource Planning (ERP)</em>tersebut tidak dapat dinilai kewajarannya;bahwa berdasarkan Analisa atas Laporan Keuangan untuk Perpajakan yang diserahkan Pemohon Banding dalam persidangan terdapat fakta Penghasilan Neto Komersial Pemohon Banding sebagai berikut :</p>			<div class='tablewrap'><table align='center' border='1' cellpadding='0' cellspacing='0'>				<tbody>					<tr>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>Tahun</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2002</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2003</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2004</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2005</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2006</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2007</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div style='text-align: center;'>2008</div>						</div></td>					</tr>					<tr>						<td style='text-align: justify; vertical-align: top; width: 5px;'><div class='wi'>						<div>Net Income&nbsp;before Tax(USD)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(4,900,700.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(3,673,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(7,419,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(2,903.000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(1,053,000.00)</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>5,774,000.00</div>						</div></td>						<td style='text-align: justify; vertical-align: top; white-space: nowrap; width: 5px;'><div class='wi'>						<div>(3,148,000.00)</div>						</div></td>					</tr>				</tbody>			</table></div>			<p>bahwa berdasarkan fakta dan data tersebut, Pemohon Banding hanya mengalami laba secara komersial pada Tahun 2007 sebesar USD 5,774,000.00, sementara Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 Pemohon Banding mengalami kerugian secara komersial;</p>			<p>bahwa akumulasi kerugian komersial (accumulated deficit) Tahun 2002, 2003, 2004, 2005, 2006 dan 2008 berjumlah USD 23,096,700.00;</p>			<p>bahwa dalam kondisi demikian menurut pendapat Majelis,pembayaran biaya <em>Enterprise Resource Planning (ERP)</em>secara terus-menerus setiap tahun yang dilakukan oleh Pemohon Banding kepada Halliburton Energy Services Inc. yang merupakan pihak yang memiliki hubungan istimewa adalah sesuatu yang tidak wajar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan-pertimbangan Majelis sebagaimana tersebut di atas, Majelisberkesimpulan bahwa koreksi Terbanding atas biaya <em>Enterprise Resource Planning (ERP) </em>sebesar USD791,784.00 tetap dipertahankan;</p>			<p>bahwa berdasarkan hasil pemeriksaan, pertimbangan dan kesimpulan Majelis atas koreksi <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 dan biaya <em>Enterprise Resource Planning (ERP) </em><em> </em>sebesar USD791,784.00sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015, dapat disimpulkan bahwa kedua biaya tersebut merupakan biaya yang tidak wajar dan kewajaran biaya tersebut tidak dapat diyakini oleh Majelis sehingga Majelis tetap mempertahankan kedua koreksi tersebut;</p>			<p>bahwa Majelis berkesimpulan <em>Intercompany Technical Assistance Fee</em>danbiaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan Pemohon Banding kepada pihak Halliburton Energy Services Inc. bukan merupakan objek yang terutang Pajak Pertambahan Nilai sehingga koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tetap dipertahankan;</p>			<p><strong>Pendapat Berbeda (Dissenting Opinion)</strong></p>			<p>bahwa terhadap koreksinegatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00), Hakim Anggota Sartono, S.H., M.Si.memberikan pendapat dan pertimbangan yang berbeda sebagai berikut :</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si.,Terbanding melakukan koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00)karena merupakan konsistensi dari adanya koreksi pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) Fee pada sengketa koreksi Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si., Terbanding menetapkan pembayaran objek sebesar Rp49.191.835.605,00 bukan merupakan pembayaran Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP), melainkan pembayaran dividen kepada pemegang saham sehingga bukan merupakan objek PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean;</p>			<p>bahwa oleh karena koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak terwujud Dari Luar Daerah Pabean bersumber dari koreksi positif atas kedua biaya tersebut di atas, maka pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. terhadap sengketa ini mengikuti hasil pemeriksaan Hakim Anggota Sartono, S.H., M.Si. terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) pada sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008;</p>			<p>bahwa sengketa Penghasilan Netto PPh Badan Tahun Pajak 2008 berupa koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) telah diputus oleh Pengadilan Pajak dengan Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015;</p>			<p>bahwa hasil pemeriksaan, pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. terhadap koreksi Intercompany Technical Assistance Fee (ITAF) dan Enterprise Resource Planning (ERP) sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015 adalah sebagai berikut :<br>			koreksi positif Intercompany Technical Assistance Fee sebesar USD5,349,708.00</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., berdasarkan <em>Amanded and Restated Tech Fee Agreement </em>tanggal 01 Januari 2002 (P.5)<em>, </em>Pemohon Banding membayar <em>Intercompany Technical Assistance Fee</em>kepada Halliburton Energy Services Inc. yang pada Tahun Pajak 2008 dibayar sebesar USD5,349,708.00 adalah pembayaran atas penggunaan atau pemanfaatan seluruh <em>patented and non-patented technology, software, technical and non-technical trade secrets and know- how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen berupa <em>Amanded and Restated Tech Fee Agreement</em>(P.5)<em>, </em>tagihan/invoice (P.72), pembayaran PPN Jasa Kena Pajak Luar Negeri (P.60), Pemotongan PPh Pasal 26 (P.66), General Ledger – Intercompany Expenses Period January to December 2008 (P.65), G/L Account Bo.141200 Intercompany Advances - Company Code 8054 (P.67) dan Transfer Pricing Study Report (P.59), Hakim Anggota Sartono, S.H., M.Si. berkeyakinan bahwa pembayaran Intercompany Technical Assistance Fee kepada Halliburton Energy Services Inc. bukan merupakan pembayaran dividen (pembagian laba) melainkan pembayaran sehubungan dengan penggunaan : <em>patented and non-patented technology, software, technical and non-technical trade secrets and know-how, scientific information, managemen expertise, business methods, techniques, plans, marketing information and other proprietary information as wel as certain trade mark trade names and services mark </em>yang dikuasai oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., jasa yang dilakukan oleh Pemohon Banding yang terkait dengan bidang pertambangan minyak dan gas bumi sangat membutuhkan teknologi, metode, serta perangkat lain yang spesifik yang tidak dimiliki oleh Pemohon Banding dan hanya dimiliki oleh pihak tertentu;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., pembayaran <em>Intercompany Technical Assistance Fee</em>dapat dibebankan sebagai biaya sepanjang biaya tersebut dikeluarkan dalam rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;bahwa berdasarkan hal-hal tersebut, Hakim Anggota Sartono, S.H., M.Si. berpendapat bahwa, <em>Intercompany Technical Assistance Fee </em>yang dibayarkan kepada Halliburton Energy Services Inc. merupakan biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa antara Pemohon Banding dengan Halliburton Energy Services Inc. memiliki hubungan istimewa karena Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa transaksi antar pihak yang memiliki hubungan istimewa diatur dalam Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17Tahun 2000;</p>			<p>bahwa Pasal 18 ayat (3) a quo tidak menghilangkan eksistensi biaya yang dikeluarkan oleh Pemohon Banding dalam rangka pembayaran kepada pihak yang memiliki hubungan istimewa, namun biaya tersebut harus wajar dan lazim sebagaimana transaksi dengan pihak-pihak yang tidak memiliki hubungan istimewa serta biaya tersebut harus terkait dengan kegiatan usaha Pemohon Banding;</p>			<p>bahwa Terbanding seharusnya menentukan biaya yang wajar yang harus dikeluarkan oleh Pemohon Banding berdasarkan data pembanding yang dimiliki oleh Terbanding yaitu transaksi atau biaya yang sama yang dibayar oleh Pemohon Banding atau perusahaan lain kepada pihak-pihak yang tidak memiliki hubungan istimewa dengan Pemohon Banding ataupun dengan perusahaan lain tersebut;</p>			<p>bahwa dalam persidangan, Terbanding tidak dapat menunjukkan data pembanding tersebut sehingga tidak dapat menentukan berapa nilai wajar yang seharusnya dibayarkan oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. untuk membayar <em>Intercompany Technical Assistance Fee</em>;</p>			<p>bahwa berdasarkan Transfer Pricing Study Report, Hakim Anggota Sartono, S.H., M.Si. dapat meyakini bahwa pembayaran <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan transaksi yang wajar dan lazim dalam dunia usaha Pemohon Banding di bidang jasa pertambangan minyak dan gas bumi;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen-dokumen dalam persidangan serta berdasarkan pertimbangan-pertimbangan Hakim Anggota Sartono, S.H., M.Si., Hakim Anggota Sartono, S.H., M.Si. berkesimpulan <em>bahwa Intercompany Technical Assistance Fee </em>yang dibayarkan sebesar USD5,349,708.00 oleh Pemohon Banding telah sesuai dengan Pasal 6 ayat (1) huruf a dan Pasal 18 ayat (3)Undang-Undang Nomor 7Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 sehingga koreksi Terbanding sebesar USD5,349,708.00 tidak dapat dipertahankan;</p>			<p>Koreksi positif biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em> </em><em>(ERP)</em><em> </em>sebesar USD791,784.00</p>			<p>bahwa menurut Hakim Anggota Sartono, S.H., M.Si.,biaya <em>Enterprise Resource Planning (ERP)</em>dibayar oleh Pemohon Banding kepada Halliburton Energy Services Inc. berdasarkan Global ERP Platform Agreement antara Pemohon Banding dengan Halliburton Energy Services Inc.;</p>			<p>bahwa Pemohon Banding membayar biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em> </em><em>(ERP)</em>kepada Halliburton Energy Services Inc. atas penggunaan <em>software</em><em> </em>yang dikembangkan oleh Halliburton Energy Services Inc.;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen berupa Global ERP Platform Agreement (P.7)<em>, </em>tagihan/invoice (P.62), pembayaran PPN BKP tak berwujud dari luar pabean (P.47), Pemotongan PPh Pasal 26 (P.63), ERP Development Summary (P.70), General Ledger-Miscellaneous Expenses (P.64) dan Transfer Pricing Study Report (P.59), Hakim Anggota Sartono, S.H., M.Si. berkeyakinan bahwa pembayaran <em>Enterprise Resource Planning (ERP)</em>kepada Halliburton Energy Services Inc. bukan merupakan pembayaran dividen (pembagian laba) melainkan pembayaran sehubungan dengan penggunaan software yang dikuasai dan dimiliki oleh Halliburton Energy Services Inc.;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., Pemohon Banding sebagai anak perusahaan dari grup Halliburton sangat membutuhkan suatu sistem yang terkoneksi dengan grup usahanya serta sistem yang akan mempermudah operasional perusahaan;</p>			<p>bahwa berdasarkan Global ERP Platform Agreement, sistem yang disediakan dari software tersebut adalah : <em>Financial Accounting, Controlling (Cost Centre Accounting), Fixed Assets Management, Project Sistem, Materials Management, Production Planning, Sales and Distribution, Plant Maintenance, Quality Management, dan Human Resources</em>;</p>			<p>bahwa menurut pendapat Hakim Anggota Sartono, S.H., M.Si., pembayaran <em>Enterprise Resource Planning (ERP)</em>dapat dibebankan sebagai biaya sepanjang biaya tersebut dikeluarkan dalam rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa berdasarkan hal-hal tersebut di atas, Hakim Anggota Sartono, S.H., M.Si.berpendapat bahwa biaya <em>Enterprise Resource Planning (ERP) </em>yang dibayarkan kepada Halliburton Energy Services Inc. merupakan biaya untuk mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa antara Pemohon Banding dengan Halliburton Energy Services Inc. memiliki hubungan istimewa karena Halliburton Energy Services Inc. memiliki 80 persen saham Pemohon Banding;</p>			<p>bahwa transaksi antar pihak yang memiliki hubungan istimewa diatur dalam Pasal 18 ayat (3) Undang-Undang Nomor 7 Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000;</p>			<p>bahwa Pasal 18 ayat (3) a quo tidak menghilangkan eksistensi biaya yang dikeluarkan oleh Pemohon Banding dalam rangka pembayaran kepada pihak yang memiliki hubungan istimewa, namun biaya tersebut haruslah wajar dan lazim sebagaimana transaksi dengan pihak-pihak yang tidak memiliki hubungan istimewa danbiaya tersebut terkait dengan kegiatan usaha Pemohon Banding;</p>			<p>bahwa Terbanding seharusnya menentukan biaya yang wajar yang harus dikeluarkan oleh Pemohon Banding berdasarkan data pembanding yang dimiliki oleh Terbanding yaitu transaksi atau biaya yang sama yang dibayar oleh Pemohon Banding atau perusahaan lain kepada pihak-pihak yang tidak memiliki hubungan istimewa dengan Pemohon Banding ataupun dengan perusahaan lain tersebut;</p>			<p>bahwa dalam persidangan, Terbanding tidak dapat menunjukkan data pembanding tersebut sehingga tidak dapat menentukan berapa nilai wajar yang seharusnya dibayarkan oleh Pemohon Bandingkepada pihak Halliburton Energy Services Inc. untuk membayar biaya <em>Enterprise</em><em> </em><em>Resource</em><em> </em><em>Planning</em><em>(ERP);</em></p>			<p>bahwa berdasarkan Transfer Pricing Study Report, Hakim Anggota Sartono, S.H., M.Si. dapat meyakini bahwa pembayaran biaya <em>Enterprise Resource Planning (ERP) </em>sebesar USD791,784.00oleh Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan transaksi yang wajar dan lazim bagi Pemohon Banding sebagai salah satu anak perusahaan dari grup Halliburton;</p>			<p>bahwa berdasarkan bukti-bukti dan dokumen-dokumen dalam persidangan serta berdasarkan pertimbangan-pertimbangan Hakim Anggota Sartono, S.H., M.Si., Hakim Anggota Sartono, S.H., M.Si. berkesimpulan bahwa biaya <em>Enterprise Resource Planning (ERP) </em>yang dibayarkan sebesar USD791,784.00oleh Pemohon Banding telah sesuai dengan Pasal 6 ayat (1) huruf a dan Pasal 18 ayat (3)Undang-Undang Nomor 7Tahun 1983 tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 sehingga koreksi Terbanding sebesar USD791,784.00tidak dapat dipertahankan;</p>			<p>bahwa berdasarkan hasil pemeriksaan, pertimbangan dan kesimpulan Hakim Anggota Sartono, S.H., M.Si. atas koreksi <em>Intercompany Technical Assistance Fee </em>sebesar USD5,349,708.00 dan biaya <em>Enterprise Resource Planning (ERP) </em><em> </em>sebesar USD791,784.00sebagaimana diuraikan dalam Putusan Pengadilan Pajak Nomor Put.59180/PP/M.XVB/15/2015, dapat disimpulkan bahwa kedua biaya tersebut merupakan biaya yang wajar dan lazim sesuai dengan Pasal 18 ayat (3) Undang-Undang Nomor 7Tahun 1983tentang Pajak Penghasilan sebagaimana telah diubah dengan Undang-Undang Nomor 17 Tahun 2000 dan dikeluarkan rangka mendapatkan, menagih dan memelihara penghasilan sebagaimana diatur Pasal 6 ayat (1) huruf a Undang-Undang a quo sehingga Hakim Anggota Sartono, S.H., M.Si. tidak dapat mempertahankan kedua koreksi tersebut;</p>			<p>bahwa Hakim Anggota Sartono, S.H., M.Si. berkesimpulan <em>Intercompany Technical Assistance Fee</em>danbiaya <em>Enterprise Resource Planning (ERP)</em>yang dibayarkan Pemohon Banding kepada pihak Halliburton Energy Services Inc. merupakan objek yang terutang Pajak Pertambahan Nilai sehingga koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tidak dapat dipertahankan;</p>			<p>bahwa menurut pendapat Majelis, Terbanding melakukan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 karena Terbanding menetapkan objek sebesar Rp49.191.835.605,00 sebagai objek yang bukan merupakan Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean, sehingga PPN yang telah disetor oleh Pemohon Banding atas objek tersebut tidak dapat dikreditkan sebagai Pajak Masukan;</p>			<p>bahwa menurut Pemohon Banding, Pemohon Banding nyata-nyata telah melakukan pembayaran PPN dan melaporkan dalam SPT Masa PPN atas pemanfaatan JKP dan/atau BKP Tidak Berwujud dari Luar Daerah Pabean dan pembayaran PPN atas pemanfaatan jasa dari Luar Daerah Pabean yang Pemohon Banding lakukan merupakan Kredit Pajak yang sah;</p>			<p>bahwa Majelis melakukan pemeriksaan terhadap dokumen dan bukti-bukti berupa Surat Setoran Pajak (SSP) pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean atas lawan transaksi Halliburton Energy Services Inc. (P.6) dan Invoice dari Halliburton Energy Services Inc. terkait dengan pembayaran <em>Intercompany Technical Assistance Fee </em>dan <em>Entreprise Resource Planning (ERP) Fee</em>(P.22);</p>			<p>bahwa dari pemeriksaan Majelis, diketahui bahwa Surat Setoran Pajak sebesar Rp19.059.940.656,00 merupakan pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean yang dibayarkan ke kas negara melalui Citibank Jakarta;</p>			<p>bahwa dengan tidak diakuinya objek sebesar Rp49.191.835.605,00 sebagai Dasar Pengenaan Pajak PPN BKP tidak berwujud dari Luar Daerah Pabean oleh Terbanding, tidak serta merta pajak yang telah disetor oleh Pemohon Banding secara sah kepada kas negara atas objek sebesar Rp49.191.835.605,00 menjadi tidak sah dan tidak dapat dikreditkan;</p>			<p>bahwa menurut pendapat Majelis, dengan berkurangnya Dasar Pengenaan Pajak PPN BKP tidak berwujud dari Luar Daerah Pabean yang terutang pajak menurut Terbanding dan Pemohon Banding sudah membayar seluruh PPN atas objek tersebut, maka telah terjadi kelebihan pembayaran pajak oleh Pemohon Banding;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan Majelis sebagaimana tersebut di atas, Majelis berkesimpulan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 tidak dapat dipertahankan;</p>			<p><strong>Pendapat Berbeda (Dissenting Opinion)</strong></p>			<p>bahwa koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00, Hakim AnggotaSartono, S.H., M.Si.memberikan pendapat dan pertimbangan yang berbeda sebagai berikut :</p>			<p>bahwa. kesimpulan Hakim AnggotaSartono, S.H., M.Si atas koreksi negatif Dasar Pengenaan Pajak PPN Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 sebesar (Rp49.191.835.605,00) tidak dapat mempertahankan koreksi tersebut;</p>			<p>bahwa berdasarkan hal tersebut, Dasar Pengenaan Pajak menurut Hakim AnggotaSartono, S.H., M.Si adalah Dasar Pengenaan Pajak berdasarkan SPT Masa PPN Pemohon Banding sebesar Rp190.599.406.560,00 dan PPN yang terutang adalah sebesar Rp.19.059.940.656,00;</p>			<p>bahwa Hakim AnggotaSartono, S.H., M.Si melakukan pemeriksaan terhadap dokumen dan bukti-bukti berupa Surat Setoran Pajak (SSP) pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean atas lawan transaksi Halliburton Energy Services Inc. (P.6) dan Invoice dari Halliburton Energy Services Inc. terkait dengan pembayaran <em>Intercompany Technical Assistance Fee </em>dan <em>Entreprise Resource Planning (ERP) Fee</em>(P.22);</p>			<p>bahwa dari pemeriksaan Hakim AnggotaSartono, S.H., M.Si, diketahui bahwa Surat Setoran Pajak sebesar Rp19.059.940.656,00 merupakan pembayaran PPN BKP tidak berwujud dari Luar Daerah Pabean yang dibayarkan ke kas negara melalui Citibank Jakarta;</p>			<p>bahwa dengan demikian Kredit Pajak sebesar Rp19.059.940.656,00 merupakan kredit pajak yang sah dan dapat dikreditkan dengan Pajak Masukan sepanjang memenuhi ketentuan perundang-undangan perpajakan yang berlaku;</p>			<p>bahwa menurut pendapat Hakim AnggotaSartono, S.H., M.Si, pengkreditan Pajak Masukan sebesar Rp19.059.940.656,00 telah sesuai dengan Pasal 9 ayat (2), 9 ayat (3) 9 ayat (9) dan Pasal 13 ayat (6) Undang-Undang Nomor 8 Tahun 1983 tentang Pajak Pertambahan Nilai Barang dan Jasa dan Pajak Penjualan Atas Barang Mewah sebagaimana telah diubah dengan Undang-Undang Nomor 18 Tahun 2000 jo. Keputusan Direktur Jenderal Pajak Nomor : KEP-522/PJ/2000 tanggal 6 Desember 2000 tentang Dokumen-Dokumen Tertentu Yang Diperlakukan Sebagai Faktur Pajak Standar;</p>			<p>bahwa berdasarkan fakta-fakta dan pertimbangan Hakim AnggotaSartono, S.H., M.Si sebagaimana tersebut di atas, Hakim AnggotaSartono, S.H., M.Si berkesimpulan koreksi positif Kredit Pajak berupa Pajak Masukan sebesar Rp4.919.183.561,00 tidak dapat dipertahankan;</p>			</div></td>		</tr>	</tbody></table><p style='text-align:center'><strong>MENIMBANG</strong><br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai Tarif Pajak;<br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai Sanksi Administrasi, kecuali bahwa besarnya sanksi administrasi tergantung pada penyelesaian sengketa lainnya;<br>bahwa atas hasil pemeriksaan dalam persidangan dan berdasarkan suara terbanyak, Majelis berketetapan untuk menggunakan kuasa Pasal 80 ayat (1) huruf b Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak untuk mengabulkan sebagian banding Pemohon Banding dengan Dasar Pengenaan Pajak menurut Majelis sebesar Rp141.407.570.955,00 dan Kredit Pajak menurut Majelis sebagai berikut :<br>Kredit Pajak menurut Terbanding Rp 14.140.757.095,00<br>Koreksi yang tidak dapat dipertahankan Rp 4.919.183.561,00<br>Kredit Pajak menurut Majelis Rp 19.059.940.656,00</p><p style='text-align:center'><strong>MENGINGAT</strong><br>Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, dan ketentuan perundang-undangan lainnya serta peraturan hukum yang berlaku dan yang berkaitan dengan sengketa ini;</p><p style='text-align:center'><strong>MEMUTUSKAN</strong><br><strong>Mengabulkan sebagian&nbsp;</strong>banding Pemohon Banding terhadap Keputusan Terbanding Nomor: KEP-3253/WPJ.07/2011 tanggal 22 Desember 2011tentang Keberatan atas Surat Ketetapan Pajak Nihil Pajak Pertambahan Nilai Barang dan Jasa Atas Pemanfaatan BKP Tidak Berwujud Dari Luar Daerah Pabean Masa Pajak Januari sampai dengan Desember 2008 Nomor: 00005/567/08/056/10 tanggal 30 September 2010, atas nama : <strong>PT XXX</strong>, dengan perhitungan menjadi sebagai berikut :<br>Dasar Pengenaan Pajak :Pemanfaatan BKP tidak berwujud dari Luar Daerah Pabean Rp141.407.570.955,00<br>PPN yang harus dipungut / dibayar sendiri Rp 14.140.757.095,00<br>Jumlah Pajak yang dapat diperhitungkan (Rp 19.059.940.656,00)<br>Jumlah PPN Kurang (lebih) dibayar (Rp 4.919.183.561,00)</p><p style='text-align:center'>Demikian diputus di Jakarta berdasarkan suara terbanyak setelah pemeriksaan dalam persidangan yang dicukupkan pada hari Rabu tanggal 06 Februari 2013, oleh Hakim Majelis XV Pengadilan Pajak yang ditunjuk dengan Penetapan Ketua Pengadilan Pajak Nomor : Pen.00843/PP/PM/VIII/2012 tanggal 03 Agustus 2012 dengan susunan Majelis dan Panitera Pengganti sebagai berikut :<br>Drs. Tonggo Aritonang, Ak., M.Sc. Sebagai Hakim Ketua,<br>Drs. Didi Hardiman, Ak. Sebagai Hakim Anggota,<br>Sartono, S.H., M.Si. Sebagai Hakim Anggota,<br>M.R. Abdi Nugroho Sebagai Panitera Pengganti,</p><p style='text-align:center'>Putusan Nomor : Put.59334/PP/M.XV/16/2015diucapkan dalam sidang terbuka untuk umum oleh Hakim Ketua pada hari Rabu tanggal4 Februari 2015dengan susunan Majelis dan Panitera Pengganti sebagai berikut:<br>Drs. Tonggo Aritonang, Ak., M.Sc. Sebagai Hakim Ketua,<br>Drs. Didi Hardiman, Ak. Sebagai Hakim Anggota,<br>Djangkung Sudjarwadi, S.H., L.L.M. Sebagai Hakim Anggota,<br>Aditya Agung Priyo Nugroho Sebagai Panitera Pengganti,</p><p style='text-align:center'>dengan dihadiri oleh para Hakim Anggota, Panitera Pengganti, dihadiri oleh Terbanding serta dihadiri oleh Pemohon Banding.</p></div>";
					//$tes .= "<div style='height: 335px;' class='nocompare-content nocompare-content-pp' id='nocompare-wrapper-pp'><p class=\"head headtop\"><strong>Putusan Pengadilan Pajak Nomor : Put-60233/PP/M.XI.B/16/2015</strong></p><p style=\"text-align:center\"><strong>RISALAH</strong><br>Putusan Pengadilan Pajak Nomor : Put-60233/PP/M.XI.B/16/2015</p><p style=\"text-align:center\"><strong>JENIS PAJAK</strong><br>Pajak Pertambahan Nilai</p><p style=\"text-align:center\"><strong>TAHUN PAJAK</strong><br>2010</p><p style=\"text-align:center\"><strong>POKOK SENGKETA</strong><br>bahwa yang menjadi pokok sengketa adalah pengajuan gugatan terhadap koreksi Pajak Masukan yang dapat diperhitungkan Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 sebesar Rp536.050.458,00;</p><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">	<tbody>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p><strong>Menurut Terbanding</strong></p>			</div></td>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa dalam Pasal 26 ayat (3) UU KUP menyatakan “Keputusan Direktur Jenderal Pajak atas keberatan dapat berupa mengabulkan seluruhnya atau sebagian, menolak atau menambah besarnya jumlah pajak yang masih harus dibayar”. Hal ini secara jelas mengatur bahwa dalam keberatan, Terbanding dapat menambah jumlah pajak yang harus dibayar;</p>			</div></td>		</tr>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p style=\"text-align:justify\"><strong>Menurut Pemohon</strong></p>			</div></td>			<td style=\"text-align: justify; vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa Terbanding (Peneliti Keberatan) jelas-jelas telah melampaui kewenangannya didalam memutuskan permohonan keberatan Pemohon Banding karena tidak ada satu ketentuan pun yang mengatur dan yang memberikan wewenang kepada Terbanding (Peneliti Keberatan) untuk melakukan koreksi seperti yang telah dilakukan Terbanding kepada Pemohon Banding yaitu melakukan koreksi atas keberatan wajib pajak yang bukan obyek keberatan (sengketa). Pihak Terbanding khususnya Peneliti Keberatan tidak mempunyai dasar hukum untuk menambah dan melakukan koreksi atas faktur pajak masukan dari PT.Mitra Mandiri Serasi. Dalam hal ini Terbanding (Peneliti Keberatan) telah mengabaikan seluruh hasil pemeriksaan yang telah dilakukan oleh pejabat pemeriksa yang berwenang yaitu Tim Pemeriksa KPP Madya Tangerang yang merupakan wakil resmi Direktur Jenderal Pajak;</p>			</div></td>		</tr>		<tr>			<td style=\"vertical-align: top;\"><div class=\"wi\">			<p style=\"text-align:justify\"><strong>Menurut Majelis</strong></p>			</div></td>			<td style=\"text-align: justify; vertical-align: top;\"><div class=\"wi\">			<p>:</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">bahwa atas Surat Ketetapan Pajak Kurang Bayar Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011, Pemohon Banding mengajukan keberatan;</p>			<p style=\"text-align:justify\">bahwa didalam perhitungan Surat Ketetapan Pajak Kurang Bayar a quo, Pemohon Banding menyatakan pajak yang dapat diperhitungkan menurut Pemohon Banding adalah Rp9.382.447.030,00, sedangkan menurut Terbanding adalah Rp9.328.934.680,00, dengan demikian koreksi Terbanding adalah sebesar Rp 53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa dengan demikian pokok sengketa keberatan Pemohon Banding adalah koreksi Pajak Masukan yang menurut Pemohon Banding seharusnya dapat diperhitungkan adalah sebesar Rp53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa atas keberatan Pemohon Banding sebesar Rp 53.512.350,00 tersebut, Terbanding menerbitkan Keputusan Nomor KEP-306/WPJ.08/2013 tanggal 13 Februari 2013, dengan menambah koreksi Pajak Masukan sebesar Rp482.538.108,00 sehingga total koreksi Pajak Masukan menjadi Rp536.050.458,00;</p>			<p style=\"text-align:justify\">bahwa alasan Terbanding menambah koreksi karena berdasarkan penjelasan pada surat ND-91/WPJ.08/BD.04/2012 tanggal 13 Desember 2012, ZZZ NPWP 21.066.167.4-401.001 sedang dilakukan pemeriksaan Bukti Permulaan;</p>			<p style=\"text-align:justify\">bahwa berdasarkan peninjauan lapangan ke alamat ZZZ NPWP 21.066.167.4-401.001 yang dituangkan dalam Laporan Hasil Penelitian Lapangan Nomor LAP-232/WPJ.08/2013 tanggal 25 Januari 2013 sesuai dengan Surat Edaran Ddirektur Jenderal Pajak Nomor SE-132/PJ/2010 tanggal 30 November 2010 ZZZandiri Serasi diindikasikan sebagai penerbit faktur pajak tidak sah;</p>			<p style=\"text-align:justify\">bahwa Terbanding berpendapat keputusan Direktur Jenderal Pajak atas keberatan dapat menambah besarnya pajak yang harus dibayar sesuai dengan Pasal 26 ayat (4) Undang-Undang Nomor 6 Tahun 1983 tentang Ketentuan Umum dan Tata Cara Perpajakan sebagaimana telah beberapa kali diubah terakhir dengan Undang-Undang Nomor 16 Tahun 2009;</p>			<p style=\"text-align:justify\">bahwa atas Keputusan Nomor KEP-306/WPJ.08/2013 tanggal 13 Februari 2013 tersebut Pemohon Banding mengajukan banding dengan Surat Nomor 014/PNG-Pjk/IV/2013 tanggal 8 Mei 2013;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian di atas dan penjelasan beserta bukti yang disampaikan oleh Pemohon Banding dan Terbanding didalam persidangan, Majelis berpendapat sebagai berikut :</p>			<p style=\"text-align:justify\">bahwa atas Pajak Masukan sebesar Rp 9.328.934.680,00 telah diperiksa dan diakui oleh Terbanding yang dituangkan dalam Surat Ketetapan Pajak Kurang Bayar Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011;</p>			<p style=\"text-align:justify\">bahwa atas koreksi Pajak Masukan sebesar Rp 53.512.350,00 berdasarkan klarifikasi peneliti keberatan mendapat jawaban “ada” sebanyak 24 Faktur Pajak dengan nilai Rp 53.512.350,00;</p>			<p style=\"text-align:justify\">bahwa atas dalil Terbanding tentang ZZZ diindikasikan sebagai penerbit faktur pajak tidak sah, sampai dengan sidang pemeriksaan dicukupkan tidak terdapat putusan Pengadilan yang telah mempunyai kekuatan hukum tetap, yang menyatakan ZZZ dipidana dibidang perpajakan atau tindak pidana lainnya yang dapat menimbulkan kerugian pada pendapatan negara, melainkan masih berstatus sebagai terperiksa “Bukti Permulaan”, oleh karena itu indikasi Pengusaha Kena Pajak penjual yaitu ZZZ sebagai penerbit faktur pajak fiktif tersebut tidak dapat dijadikan landasan hukum atas koreksi Terbanding;</p>			<p style=\"text-align:justify\">bahwa sesuai dengan Pasal 13 ayat (5) jo. Pasal 15 ayat (4) Undang-Undang Nomor 6 Tahun 1983 tentang Ketentuan Umum dan Tata Cara Perpajakan sebagaimana telah beberapa kali diubah terakhir dengan Undang-Undang Nomor 16 Tahun 2009, Terbanding dapat menerbitkan Surat Ketetapan Pajak atau Surat Ketetapan Pajak Kurang Bayar Tambahan terhadap ZZZ;</p>			<p style=\"text-align:justify\">bahwa Faktur Pajak a quo yang diterbitkan oleh ZZZ dapat menjadi “batal” dalam hal ZZZ terbukti menerbitkan faktur pajak tidak sah dan telah ada putusan pengadilan yang berkekuatan hukum tetap;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian di atas, Majelis berpendapat alasan koreksi Terbanding tidak memiliki alasan dan dasar hukum yang kuat, sehingga Majelis berkesimpulan tidak mempertahankan koreksi Terbanding sebesar Rp 536.050.458,00;</p>			<p style=\"text-align:justify\">bahwa berdasarkan uraian tersebut di atas, rekapitulasi pendapat Majelis atas pokok sengketa adalah sebagai berikut :</p>			<div class=\"tablewrap\"><table align=\"center\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">				<tbody>					<tr>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>No</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Uraian Koreksi</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Total Sengketa&nbsp;(Rp)</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Tidak&nbsp;Dipertahankan&nbsp;(Rp)</strong></p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\"><strong>Dipertahankan&nbsp;(Rp)</strong></p>						</div></td>					</tr>					<tr>						<td><div class=\"wi\">						<p style=\"text-align:justify\">1</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:justify\">Pajak Masukan</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">536.050.458,00</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">536.050.458,00</p>						</div></td>						<td><div class=\"wi\">						<p style=\"text-align:right\">0,00</p>						</div></td>					</tr>				</tbody>			</table></div>			</div></td>		</tr>	</tbody></table><p style=\"text-align:center\"><strong>MENIMBANG</strong><br>bahwa dalam sengketa banding ini tidak terdapat sengketa mengenai sanksi administrasi kecuali besarnya sanksi administrasi tergantung pada penyelesaian sengketa lainnya;</p><p style=\"text-align:center\">bahwa berdasarkan kesimpulan Majelis terhadap sengketa di atas, maka dengan kuasa Pasal 80 ayat (1) huruf b Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, Majelis memutuskan untuk mengabulkan sebagian banding Pemohon Banding, sehingga Pajak Masukan dihitung kembali sebagai berikut :<br>Jumlah Pajak Masukan menurut Terbanding sebesar Rp 8.846.396.572,00Jumlah Pajak Masukan Yang tidak dapat dipertahankan sebesar Rp 536.050.458,00Jumlah Pajak Masukan menurut Majelis sebesar Rp 9.382.447.030,00</p><p style=\"text-align:center\"><strong>MENGINGAT</strong><br>Undang-Undang Nomor 14 Tahun 2002 tentang Pengadilan Pajak, dan ketentuan perundang-undangan lainnya serta peraturan hukum yang berlaku dan yang berkaitan dengan perkara ini;</p><p style=\"text-align:center\"><strong>MEMUTUSKAN<br>Menyatakan mengabulkan</strong> seluruhnya banding Pemohon Banding terhadap Keputusan Direktur Jenderal Pajak Nomor <strong>KEP-306/WPJ.08/2013 </strong><strong> </strong>tanggal <strong>13</strong><strong>Februari</strong><strong> </strong><strong>2013</strong><strong> </strong>tentang Keberatan Wajib Pajak atas Surat Ketetapan Pajak Kurang Bayar (SKPKB) Pajak Pertambahan Nilai Barang dan Jasa Masa Pajak Maret 2010 Nomor 00015/207/10/415/11 tanggal 12 Desember 2011, atas nama <strong>XXX</strong>, sehingga dihitung kembali menjadi sebagai berikut :</p><table class=\"tablecontent\" style=\"line-height:1.6\" align=\"center\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">	<tbody>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:center\"><strong>Dasar Pengenaan Pajak :</strong></p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:center\">&nbsp;</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Ekspor</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Rp 205.899.833.380,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Penyerahan yang PPN-nya harus dipungut sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;6.166.426.760,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Penyerahan yang PPN-nya tidak dipungut</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;1.303.155.000,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Jumlah Dasar Pengenaan Pajak</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp 213.369.415.140,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Pajak Keluaran yang harus dipungut sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; 616.642.676,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Kredit Pajak :</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:justify\">&nbsp;</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Pajak Masukan yang dapat diperhitungkan</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;9.382.447.030,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Surat Tagihan Pajak (pokok kurang bayar)</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">- Dibayar dengan NPWP sendiri</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Jumlah pajak yang dapat diperhitungkan</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;9.382.447.030,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">PPN Kurang (Lebih) Bayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;8.765.804.354,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Kelebihan Pajak yg sudah dikompensasikan ke masa berikutnya</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp;8.765.804.354,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Pajak Pertambahan Nilai yang kurang bayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">Sanksi Administrasi : Pasal 13 (3) UU KUP</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>		<tr>			<td><div class=\"wi\">			<p style=\"text-align:justify\">PPN yang masih harus/lebih dibayar</p>			</div></td>			<td><div class=\"wi\">			<p style=\"text-align:right\">Rp &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0,00</p>			</div></td>		</tr>	</tbody></table><p style=\"text-align:center\">Demikian diputus di Jakarta pada hari Rabu, tanggal 5 Februari 2014 berdasarkan musyawarah Majelis XI B Pengadilan Pajak dengan susunan Majelis dan Panitera Pengganti sebagai berikut:<br>I Putu Setiawan sebagai Hakim Ketua,<br>I Made Sudana sebagai Hakim Anggota,<br>&nbsp;Arif Subekti sebagai Hakim Anggota,<br>Esti Cahya Inteni sebagai Panitera Pengganti,</p><p style=\"text-align:center\">Putusan Nomor Put.60233/PP/M.XI.B/16/2015 diucapkan dalam sidang terbuka untuk umum oleh Hakim Ketua pada hari Rabu tanggal 18 Maret 2015 dengan susunan Majelis dan Panitera Pengganti berdasarkan Penetapan Ketua Pengadilan Pajak Nomor Pen.008/PP/PM/III/Ucap/2015 tanggal 18 Maret 2015 sebagai berikut :<br>I Putu Setiawan sebagai Hakim Ketua,<br>Arif Subekti sebagai Hakim Anggota,<br>Masdi sebagai Hakim Anggota,<br>Esti Cahya Inteni sebagai Panitera Pengganti,</p><p style=\"text-align:center\">dengan dihadiri oleh para Hakim Anggota, Panitera Pengganti, serta tidak dihadiri oleh Terbanding dan tidak dihadiri oleh Pemohon Banding.</p></div>";
					$tes .= $body_replace;
					$tes .= "</div>";
					$tes .= "</div>";
					$tes .= "<script src='". base_url() ."assets/themes/js/jquery.min.js'></script>";
					$tes .= "<script src='". base_url() ."assets/themes/js/html2canvas.js'></script>";
					$tes .= "<script src='". base_url() ."assets/themes/js/converttable.js'></script>";
					$tes .= "<script type=\"text/javascript\">";
					$tes .= "var html2pdf = {";
					//$tes .= "header: {";
					//$tes .= "height: \"1cm\",";
					//$tes .= "contents: '<div class=\"center\">page</div>'";
					//$tes .= "},";
					$tes .= "footer: {";
					$tes .= "height:\"1.6cm\",";
					$tes .= "contents: '<div style=\"border-top:1px solid #CCC;padding-top:0.2cm;margin-top:0.2cm;height:40px;text-align:center;background:url(http://dannydarussalam.com/tax-engine/newdir/assets/docfooter.jpg) no-repeat center center;\"></div>'";
					$tes .= "}";
					$tes .= "};";
					$tes .= "</script>";
					$tes .= "</body></html>";
					
					echo $tes;
				} else {
					echo 'nothing found';
				}
			} else {
				echo 'nothing happen';
			}
			
		}
		else
		{
			echo '0';
		}
	}

	public function check_favourite()
	{
		$favourite_document_id = $this->input->post('id');
		$favourite_user = $this->session->userdata('user_id');

		$check = $this->favourite_model->check($favourite_user, 1, $favourite_document_id);

		if($check == 0)
		{
			echo '0';
		}
		else
		{
			echo '1';
		}
	}

	public function favourite()
	{
		$favourite_document_id = $this->input->post('id');
		$favourite_user = $this->session->userdata('user_id');

		$check = $this->favourite_model->check($favourite_user, 1, $favourite_document_id);

		if($check == 0)
		{
			$data = array(
					'favourite_user' => $favourite_user,
					'favourite_type' => 1,
					'favourite_document_id' => $favourite_document_id,
				);
			$insert = $this->favourite_model->insert($data);

			if($insert)
			{
				echo '1';
			}
			else
			{
				echo '0';
			}
		}
		else
		{
			$favourite = $this->favourite_model->get_favourite($favourite_user, 1, $favourite_document_id);

			$favourite_id = $favourite['favourite_id'];

			$delete = $this->favourite_model->delete($favourite_id);

			if($delete)
			{
				echo '2';
			}
			else
			{
				echo '0';
			}
		}
	}

	public function lastseen()
	{
		if($this->user_auth->is_logged_in()) {
			$lastseen_document_id = $this->input->post('id');
			$lastseen_user = $this->session->userdata('user_id');

			$lastseen = $this->lastseen_model->check_last_id($lastseen_user);

			$cur_lastseen_type = $lastseen['lastseen_type'];
			$cur_lastseen_document_id = $lastseen['lastseen_document_id'];

			if(!empty($lastseen))
			{

				if($cur_lastseen_type == 1 && $cur_lastseen_document_id != $lastseen_document_id)
				{
					$data = array(
							'lastseen_user' => $lastseen_user,
							'lastseen_type' => 1,
							'lastseen_document_id' => $lastseen_document_id,
						);
					$insert = $this->lastseen_model->insert($data);
				}

			}
			else
			{
				$data = array(
						'lastseen_user' => $lastseen_user,
						'lastseen_type' => 1,
						'lastseen_document_id' => $lastseen_document_id,
					);
				$insert = $this->lastseen_model->insert($data);
			}
		} else {
			echo '0';
		}
	}
}