<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    // controller global- memanggil method costructor yang ada di CI_Controller
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation'); //untuk form validasi
    }

    public function index()
    {
        $data['title'] = 'Login Page';
        $this->load->view('templates/auth_header');
        $this->load->view('auth/login');
        $this->load->view('templates/auth_footer');
    }

    public function registration()
    {
        //rules untuk validasi >> ('[name dari form input]','[nama lain]','required')
        $this->form_validation->set_rules('name', 'Name', 'required|trim'); //trim=agar tidak memasukkan spasi berlebih ke database
        $this->form_validation->set_rules(
            'email',
            'Email',
            'required|trim|valid_email|is_unique[user.email]',
            [
                'is_unique' => 'This email has already registered!'
            ]
        ); //form_validation bisa lihat di documentasi CI >> is_unique[tabel database.field]
        $this->form_validation->set_rules(
            'password1',
            'Password',
            'required|trim|min_length[3]|matches[password2]',
            [
                'matches' => 'password dont match!',
                'min_length' => 'password too short!'
                //pesan jika tidak sesuai
            ]
        ); //min_length = untuk minimal input karakter di field
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|min_length[3]|matches[password1]'); //matches[] = untuk mencocokkan field satu dengan field lain


        //jika validasi gagal maka tampilkan form registrasi awal
        if ($this->form_validation->run() == false) {
            $data['title'] = 'User Registration';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            // echo 'data berhasil ditambahkan!';
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT), //password_hash untuk enkripsi pw, PASSWORD_DEFAULT = dipilihkan karakter enkripsi
                'role_id' => 2, //role untuk member = 2 sesuai di database
                'is_active' => 1,
                'date_created' => time()
            ];
            $this->db->insert('user', $data); //jika registrasi berhasil input ke database
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please Login</div>'); //alert jika register berhasil
            redirect('auth'); //default index
        }
    }
}
