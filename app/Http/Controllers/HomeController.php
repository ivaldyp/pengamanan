<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\SessionCheckTraits;

use App\Agenda_tb;
use App\Berita_tb;
use App\Emp_data;
use App\Sec_access;
use App\Sec_logins;
use App\Sec_menu;

session_start();

class HomeController extends Controller
{
	use SessionCheckTraits;

	public function __construct()
	{
		$this->middleware('auth');
		set_time_limit(300);
	}

	public function display_menus($query, $parent, $level = 0, $idgroup)
	{
		if ($parent == 0) {
			$sao = "(sao = 0 or sao is null)";
		} else {
			$sao = "(sao = ".$parent.")";
		}
							
		$query = DB::select( DB::raw("
					SELECT *
					FROM bpaddtfake.dbo.sec_menu
					JOIN bpaddtfake.dbo.sec_access ON bpaddtfake.dbo.sec_access.idtop = bpaddtfake.dbo.sec_menu.ids
					WHERE bpaddtfake.dbo.sec_access.idgroup = '$idgroup'
					AND bpaddtfake.dbo.sec_access.zviw = 'y'
					AND $sao
					AND bpaddtfake.dbo.sec_menu.tampilnew = 1
					ORDER BY bpaddtfake.dbo.sec_menu.urut
					"));
		$query = json_decode(json_encode($query), true);

		$result = '';

		$link = '';
		$arrLevel = ['<ul class="nav" id="side-menu">', '<ul class="nav nav-second-level">', '<ul class="nav nav-third-level">', '<ul class="nav nav-fourth-level">', '<ul class="nav nav-fourth-level">'];

		if (count($query) > 0) {

			$result .= $arrLevel[$level];

			if ($level == 0) {
				$result .= '<li id="li_portal"> <a href="/portal" class="waves-effect"> <i class="fa fa-globe fa-fw"></i> <span class="hide-menu">Portal BPAD</span></a></li>';
			}
		
			foreach ($query as $menu) {
				if (is_null($menu['urlnew'])) {
					$link = 'javascript:void(0)';
				} else {
					if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
						$link = "https"; 
					else
						$link = "http"; 
					  
					$link .= "://";       
					$link .= $_SERVER['HTTP_HOST']; 
					$link .= $menu['urlnew'];
				}

				if ($menu['child'] == 0) {
					$result .= '<li> <a href="'.$link.'" class="waves-effect"><i class="fa '. (($menu['iconnew'])? $menu['iconnew'] :'').' fa-fw"></i> <span class="hide-menu">'.$menu['desk'].'</span></a></li>';
					
				} elseif ($menu['child'] == 1) {
					$result .= '<li> <a href="'.$link.'" class="waves-effect"><i class="fa '. (($menu['iconnew'])? $menu['iconnew'] :'').' fa-fw"></i> <span class="hide-menu">'.$menu['desk'].'<span class="fa arrow"></span></span></a>';
					
					$result .= $this->display_menus($query, $menu['ids'], $level+1, $idgroup);

					$result .= '</li>';
				}
			}

			$result .= '</ul>';
		}
		return $result;
	}

	public function index(Request $request)
	{
		$this->checkSessionTime();
		
		unset($_SESSION['user_data']);

		if (is_null(Auth::user()->usname)) {
			$iduser = Auth::user()->id_emp;

			$user_data = DB::select( DB::raw("
						SELECT id_emp,a.uname+'::'+convert(varchar,a.tgl)+'::'+a.ip,createdate,nip_emp,nrk_emp,nm_emp,nrk_emp+'-'+nm_emp as c2,gelar_dpn,gelar_blk,jnkel_emp,tempat_lahir,tgl_lahir,CONVERT(VARCHAR(10), tgl_lahir, 103) AS [DD/MM/YYYY],idagama,alamat_emp,tlp_emp,email_emp,status_emp,ked_emp,status_nikah,gol_darah,nm_bank,cb_bank,an_bank,nr_bank,no_taspen,npwp,no_askes,no_jamsos,tgl_join,CONVERT(VARCHAR(10), tgl_join, 103) AS [DD/MM/YYYY],tgl_end,reason,a.idgroup,pass_emp,foto,ttd,a.telegram_id,a.lastlogin,tbgol.tmt_gol,CONVERT(VARCHAR(10), tbgol.tmt_gol, 103) AS [DD/MM/YYYY],tbgol.tmt_sk_gol,CONVERT(VARCHAR(10), tbgol.tmt_sk_gol, 103) AS [DD/MM/YYYY],tbgol.no_sk_gol,tbgol.idgol,tbgol.jns_kp,tbgol.mk_thn,tbgol.mk_bln,tbgol.gambar,tbgol.nm_pangkat,tbjab.tmt_jab,CONVERT(VARCHAR(10), tbjab.tmt_jab, 103) AS [DD/MM/YYYY],tbjab.idskpd,tbjab.idunit,tbjab.idjab, tbunit.child, tbjab.idlok,tbjab.tmt_sk_jab,CONVERT(VARCHAR(10), tbjab.tmt_sk_jab, 103) AS [DD/MM/YYYY],tbjab.no_sk_jab,tbjab.jns_jab,tbjab.idjab,tbjab.eselon,tbjab.gambar,tbdik.iddik,tbdik.prog_sek,tbdik.no_sek,tbdik.th_sek,tbdik.nm_sek,tbdik.gelar_dpn_sek,tbdik.gelar_blk_sek,tbdik.ijz_cpns,tbdik.gambar,tbdik.nm_dik,b.nm_skpd,c.nm_unit,c.notes,d.nm_lok, tbunit.sao FROM bpaddtfake.dbo.emp_data as a
							CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
							CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
							CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
							CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
							,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1' 
							and id_emp like '". Auth::user()->id_emp ."'
							"))[0];

			$user_data = json_decode(json_encode($user_data), true);
		} else {
			$iduser = Auth::user()->usname;

			$user_data = Sec_logins::
							where('usname', $iduser)
							->first();
		}

		$_SESSION['user_data'] = $user_data;

		$all_menu = [];

		$menus = $this->display_menus($all_menu, 0, 0, $_SESSION['user_data']['idgroup']);

		$_SESSION['menus'] = $menus;

		$agendas = Agenda_tb::
					where('appr', 'Y')
					->orderBy('ids', 'desc')
					->paginate(3);

		$beritas = Berita_tb::
					where('appr', 'Y')
					->orderBy('ids', 'desc')
					->paginate(3);

		$countpegawai = DB::select( DB::raw("
							SELECT count(id_emp) as total FROM bpaddtfake.dbo.emp_data as a
							CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
							CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
							,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
							and ked_emp = 'aktif' and tgl_end is null"))[0];
		$countpegawai = json_decode(json_encode($countpegawai), true);

		$countdisp = DB::select( DB::raw("SELECT count (ids) as total
							from bpaddtfake.dbo.fr_disposisi
							where to_pm = '1.20.512.19395' and rd = 'N'"))[0];
		$countdisp = json_decode(json_encode($countdisp), true);

		$countcontent = DB::select(DB::raw("SELECT count(ids) as total
								  FROM [bpadcmsfake].[dbo].[content_tb]
								  where appr = 'N' and suspend = 'N'"))[0];
		$countcontent = json_decode(json_encode($countcontent), true);

		if (isset(Auth::user()->id_emp)) {
			$idunit = $_SESSION['user_data']['idunit'];
			if (strlen($idunit) == 2) {
				// kalo kaban
				$employees = DB::select( DB::raw("  
					SELECT id_emp, nm_emp, tbjab.idjab, tbjab.idunit, tbunit.child, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and idunit like '$idunit%' AND LEN(idunit) < 10 AND ked_emp = 'AKTIF'
					ORDER BY idunit ASC, idjab ASC") );
			} elseif (strlen($idunit) == 10) {
				// kalo staf
				$employees = DB::select( DB::raw("  
					SELECT id_emp, nm_emp, tbjab.idjab, tbjab.idunit, tbunit.child, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and idunit like '$idunit' AND ked_emp = 'AKTIF'
					order by idunit ASC, nm_emp ASC") );
			} else {
				// kalo kasuban / kabid / kasubid
				$employees = DB::select( DB::raw("  
					SELECT id_emp, nm_emp, tbjab.idjab, tbjab.idunit, tbunit.child, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and idunit like '$idunit%' AND ked_emp = 'AKTIF'
					order by idunit ASC, nm_emp ASC") );
			}
		} else {
			$employees = DB::select( DB::raw("  
				SELECT id_emp, nm_emp, tbjab.idjab, tbjab.idunit, tbunit.child, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
				CROSS APPLY (SELECT TOP 1 tmt_gol,tmt_sk_gol,no_sk_gol,idgol,jns_kp,mk_thn,mk_bln,gambar,nm_pangkat FROM  bpaddtfake.dbo.emp_gol,bpaddtfake.dbo.glo_org_golongan WHERE a.id_emp = emp_gol.noid AND emp_gol.idgol=glo_org_golongan.gol AND emp_gol.sts='1' AND glo_org_golongan.sts='1' ORDER BY tmt_gol DESC) tbgol
				CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
				CROSS APPLY (SELECT TOP 1 iddik,prog_sek,no_sek,th_sek,nm_sek,gelar_dpn_sek,gelar_blk_sek,ijz_cpns,gambar,nm_dik FROM  bpaddtfake.dbo.emp_dik,bpaddtfake.dbo.glo_dik WHERE a.id_emp = emp_dik.noid AND emp_dik.iddik=glo_dik.dik AND emp_dik.sts='1' AND glo_dik.sts='1' ORDER BY th_sek DESC) tbdik
				CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
				,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
				and idunit like '01%' AND LEN(idunit) < 10 AND ked_emp = 'AKTIF'
				ORDER BY idunit ASC, idjab ASC") );
		}
		$employees = json_decode(json_encode($employees), true);

		$d_now = (int)date('d');
		$d_tom = (int)date('d',strtotime("+1 days"));
		$d_yes = (int)date('d',strtotime("-1 days"));

		$m_now = (int)date('m');
		$m_tom = (int)date('m',strtotime("+1 days"));
		$m_yes = (int)date('m',strtotime("-1 days"));

		$ultah_now = DB::select( DB::raw("
					SELECT nm_emp, tbjab.idjab, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and day(tgl_lahir) = $d_now and month(tgl_lahir) = $m_now and ked_emp = 'AKTIF'
					ORDER BY nm_emp ASC 
					"));
		$ultah_now = json_decode(json_encode($ultah_now), true);

		$ultah_tom = DB::select( DB::raw("
					SELECT nm_emp, tbjab.idjab, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and day(tgl_lahir) = $d_tom and month(tgl_lahir) = $m_tom and ked_emp = 'AKTIF'
					ORDER BY nm_emp ASC 
					"));
		$ultah_tom = json_decode(json_encode($ultah_tom), true);

		$ultah_yes = DB::select( DB::raw("
					SELECT nm_emp, tbjab.idjab, tbunit.nm_unit from bpaddtfake.dbo.emp_data as a
					CROSS APPLY (SELECT TOP 1 tmt_jab,idskpd,idunit,idlok,tmt_sk_jab,no_sk_jab,jns_jab,replace(idjab,'NA::','') as idjab,eselon,gambar FROM  bpaddtfake.dbo.emp_jab WHERE a.id_emp=emp_jab.noid AND emp_jab.sts='1' ORDER BY tmt_jab DESC) tbjab
					CROSS APPLY (SELECT TOP 1 * FROM bpaddtfake.dbo.glo_org_unitkerja_2020 WHERE glo_org_unitkerja_2020.kd_unit = tbjab.idunit) tbunit
					,bpaddtfake.dbo.glo_skpd as b,bpaddtfake.dbo.glo_org_unitkerja_2020 as c,bpaddtfake.dbo.glo_org_lokasi as d WHERE tbjab.idskpd=b.skpd AND tbjab.idskpd+'::'+tbjab.idunit=c.kd_skpd+'::'+c.kd_unit AND tbjab.idskpd+'::'+tbjab.idlok=d.kd_skpd+'::'+d.kd_lok AND a.sts='1' AND b.sts='1' AND c.sts='1' AND d.sts='1'
					and day(tgl_lahir) = $d_yes and month(tgl_lahir) = $m_yes and ked_emp = 'AKTIF'
					ORDER BY nm_emp ASC 
					"));
		$ultah_yes = json_decode(json_encode($ultah_yes), true);

		return view('home')
				->with('iduser', $iduser)
				->with('agendas', $agendas)
				->with('beritas', $beritas)
				->with('countpegawai', $countpegawai)
				->with('countdisp', $countdisp)
				->with('countcontent', $countcontent)
				->with('employees', $employees)
				->with('ultah_tom', $ultah_tom)
				->with('ultah_now', $ultah_now)
				->with('ultah_yes', $ultah_yes);
	}
}
