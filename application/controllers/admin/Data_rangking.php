<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data_rangking extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('status') != "login") {
            $this->session->set_flashdata(
                'pesan',
                '<div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                <button class="close" data-dismiss="alert">
                <span>&times;</span>
                </button>
                Login terlebih dahulu
                </div>
                </div>'
            );
            redirect(base_url("login"));
        } else if ($this->session->userdata('akses') != 'manajer' && $this->session->userdata('akses') != 'superadmin') {
            $this->session->set_flashdata(
                'pesan',
                '<div class="alert alert-warning alert-dismissible show fade">
                <div class="alert-body">
                <button class="close" data-dismiss="alert">
                <span>&times;</span>
                </button>
                Anda tidak bisa akses halaman ini!!!
                </div>
                </div>'
            );
            redirect(base_url('login'));
        }

        $this->load->model('hitung_model');
    }
    public function index()
    {
        if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
            $tahun = $_GET['tahun'];
            $data = $this->hitung_model->hitung($tahun);
            $where = array('id_periode ' => $tahun);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $thn = $p->tahun;
            }
            $ket = 'Data Siswa periode ' . $thn;
        } else {
            $thn = date("Y");
            $where = array('tahun ' => $thn);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $tahun = $p->id_periode;
            }
            $data = $this->hitung_model->hitung($tahun);
            $ket = 'Data Siswa periode ' . $thn;
        }
        $data['ket'] = $ket;
        $data['thn'] = $thn;
        $data['option_tahun'] = $this->titian_model->get_data('periode')->result();
        $this->load->view('template/header');
        $this->load->view('template/sidebar');
        $this->load->view('rank/data_rank', $data);
        $this->load->view('template/footer');
    }

    public function keputusan()
    {
        if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
            $tahun = $_GET['tahun'];
            $data = $this->hitung_model->hitung($tahun);
            $where = array('id_periode ' => $tahun);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $thn = $p->tahun;
            }
            $ket = 'Data Siswa periode ' . $thn;
            $url_cetak = 'admin/data_rangking/cetak?tahun=' . $tahun;
            $transaksi = $this->titian_model->view_by_year($tahun)->result();
        } else {
            $thn = date("Y");
            $where = array('tahun ' => $thn);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $tahun = $p->id_periode;
            }
            $data = $this->hitung_model->hitung($tahun);
            $ket = 'Data Siswa periode ' . $thn;
            $url_cetak = 'admin/data_rangking/cetak?tahun=' . $tahun;
            $transaksi = $this->titian_model->view_by_year($tahun)->result();
        }
        $data['ket'] = $ket;
        $data['thn'] = $thn;
        $data['url_cetak'] = base_url($url_cetak);
        $data['transaksi'] = $transaksi;
        $data['option_tahun'] = $this->titian_model->get_data('periode')->result();
        $this->load->view('template/header');
        $this->load->view('template/sidebar');
        $this->load->view('rank/data_keputusan', $data);
        $this->load->view('template/footer');
    }
    public function cetak()
    {
        if (isset($_GET['tahun']) && !empty($_GET['tahun'])) { // Cek apakah user telah memilih filter dan klik tombol tampilkan
            $tahun = $_GET['tahun'];
            $data = $this->hitung_model->hitung($tahun);
            $where = array('id_periode ' => $tahun);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $thn = $p->tahun;
                $gen = $p->generasi;
            }
            $ket = 'Data Siswa periode ' . $thn;
        } else {
            $thn = date("Y");
            $where = array('tahun ' => $thn);
            $periodeSiswa  = $this->titian_model->get_where_data($where, 'periode')->result();
            foreach ($periodeSiswa as $p) {
                $tahun = $p->id_periode;
                $gen = $p->generasi;
            }
            $data = $this->hitung_model->hitung($tahun);
            $ket = 'Data Siswa periode ' . $thn;
        }
        $data['ket'] = $ket;
        $data['thn'] = $thn;
        $data['gen'] = $gen;
        $w = array('periode.id_periode ' => $tahun);
        $data['nama'] = $this->titian_model->joinNilaiAlternatifWhere($w);
        $data['kriteria']    = $this->titian_model->get_data('kriteria')->result();        
        $this->load->view('rank/print', $data);
    }
    public function insert()
    {
        $id_rank    = $this->input->post('id_rank');
        foreach ($id_rank as $key => $value) {
            $data = array(
                "rangking"          => $id_rank[$key],
                "id_siswa"          => $_POST['id_siswa'][$key],
                "nilai"             => $_POST['nilai'][$key],
                "keputusan"         => $_POST['keputusan'][$key],
                'tanggal'           => date('Y-m-d H:i:s'),
            );
            echo json_encode($data);
            $this->titian_model->insert_data($data, 'rangking');
        }
        redirect('admin/data_rangking/keputusan');
    }
    public function update()
    {
        $id_rank    = $this->input->post('id_rank');
        $datar = array();
        foreach ($id_rank as $key => $value) {
            $datar[] = array(
                "id_rangking"          => $id_rank[$key],
                // "nama_siswa"        => $_POST['id_siswa'][$key],
                // "nilai"             => $_POST['nilai'][$key],
                "keputusan"         => $_POST['keputusan'][$key],
                // 'tanggal'           => date('Y-m-d H:i:s'),
            );
            // $this->titian_model->insert_data($data, 'rangking');
        }
        $this->db->update_batch('rangking', $datar, 'id_rangking');
        echo json_encode($datar);
        redirect('admin/data_rangking/keputusan');
    }
    public function delete($id)
    {
        $where = array('id_rangking' => $id);
        $this->titian_model->delete_data($where, 'rangking');
        $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        Data Rangking berhasil dihapus!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>');
        redirect('admin/data_rangking/keputusan');
    }
}
