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
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email'); //rules untuk validasi email login
        $this->form_validation->set_rules('password', 'Password', 'trim|required'); //rules untuk validasi email password

        if ($this->form_validation->run() == false) { // jika gagal validasi login
            $data['title'] = 'Login Page';
            $this->load->view('templates/auth_header');
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            //jika berhasil validasi login
            //buat private method untuk validasi berhasil
            $this->_login();
        }
    }

    private function _login()
    {
        $email  = $this->input->post('email');
        $password = $this->input->post('password');

        //buat query database untuk mencari user yang emailnya sudah terdaftar
        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        // pakai tanda => karena dalam array[]
        //db = query builder CI || get_where = (select * from)
        //->row_array = untuk ambil satu baris saja

        //jika usernya ada
        if ($user) {
            //jika usernya aktif
            if ($user['is_active'] == 1) {
                //cek password >> 
                //password_verify = untuk mencocokan antara pw yang diketik login form dengan pw yang sudah di hash/ dienkripsi di database
                //parameter1= $password, yang diambil dari kolom input password di form login 
                //dicocokan dengan parameter2 = $user['password'], yaitu password yang ada di data user yang sudah terdaftar
                if (password_verify($password, $user['password'])) {
                    //jika sama >> login
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id'] // untuk menentukan menu pada form admin dan member
                    ];
                    //simpan data di session
                    $this->session->set_userdata($data);
                    //arahkan ke controller dan view user / admin
                    redirect('user'); // ke view user
                } else {
                    //jika parameter1 dan 2 tidak sama >> gagal
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password!</div>');
                    redirect('auth');
                }
            } else {
                //jika usernya tidak aktif
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">This email has not activated!</div>'); //alert jika email tidak aktif
                redirect('auth');
            }
        } else {
            //jika tidak ada user dengan email yang diinput

            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email is not registered!</div>'); //alert jika email tidak terdaftar
            redirect('auth');
        }
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
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT), //password_hash untuk enkripsi pw, PASSWORD_DEFAULT = dipilihkan karakter enkripsi
                'role_id' => 2, //role untuk member = 2 >> sesuai di database
                'is_active' => 1,
                'date_created' => time()
            ];
            $this->db->insert('user', $data); //jika registrasi berhasil input ke database
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please Login</div>'); //alert jika register berhasil
            redirect('auth'); //default index
        }
    }


    //logout
    // tugasnya bersihkan session dan kembalikan ke halaman login
    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');

        redirect('auth');
    }
}
