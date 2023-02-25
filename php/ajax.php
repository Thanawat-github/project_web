<?php
include("function.php");
$lms = new lms();
date_default_timezone_set('Asia/Bangkok');
$date = date("Y-m-d H:i:s");
$namedate = date("YmdHis");

// if(isset($_POST['path'])&&isset($_POST['image'])){
// 	$path = $_POST['path'];
// 	$img = $_POST['image'];

// 	mkdir('../data/users/' . $path, 0777, true);

// 	define('orig_dir', '../data/users/' . $path . '/');
// 	$img = str_replace('data:image/png;base64,', '', $img);
// 	$img = str_replace(' ', '+', $img);
// 	$data = base64_decode($img);
// 	// Save image to data/original directory
// 	$fi = new FilesystemIterator(orig_dir, FilesystemIterator::SKIP_DOTS);
// 	$file = orig_dir . 'image' . iterator_count($fi). '.png';
// 	$success = file_put_contents($file, $data);
// 	print $success ? $file : 'Unable to save the file in image directory.';
// }

if (isset($_POST['stdview'])) {

	$id = $_POST['stdview'];
	$show_std = $lms->select('student', "*", "id='$id'");

	if ($show_std) {

		echo json_encode(array(
			'success' => 1,
			'result1' => $show_std[0]['std_pic'],
			'result2' => $show_std[0]['std_id'],
			'result3' => $show_std[0]['prefix'],
			'result4' => $show_std[0]['fname'],
			'result5' => $show_std[0]['lname'],
			'result6' => $show_std[0]['email'],
			'result7' => $show_std[0]['phone']
		));
	} else {
		echo json_encode(array('success' => 2));
	}
}

if (isset($_POST['timestart']) && isset($_POST['subid'])) {

	$timestart = $_POST['timestart'];
	$subid = $_POST['subid'];

	$addcroom = $lms->insert('classroom', ["id_subject" => $subid, "stime" => $timestart]);

	if ($addcroom) {

		$last_id = $lms->dbConnect->insert_id;

		echo json_encode(array('success' => 1, 'last_id' => $last_id));
	}
}

if (
	isset($_POST['cr_checkin']) && isset($_POST['std_checkin']) &&
	isset($_POST['sub_checkin']) && isset($_POST['time_checkin']) &&
	isset($_POST['capimg'])
) {
	$capimg = $_POST['capimg'];
	$cr_checkin = $_POST['cr_checkin'];
	$std_checkin = $_POST['std_checkin'];
	$sub_checkin = $_POST['sub_checkin'];
	$time_checkin = $_POST['time_checkin'];

	$chekstdsub = $lms->select('sub_std', "*", "id_subject='$sub_checkin' AND id_student = '$std_checkin'");

	if ($chekstdsub) {

		$checkstd = $lms->select('checkin', "*", "id_croom='$cr_checkin' AND id_std = '$std_checkin'");

		if (!$checkstd) {

			// $uploadpath   = '../upload/img_checkin/';
			// $parts        = explode(";base64,", $capimg);
			// $imageparts   = explode("image/", @$parts[0]);
			// $imagetype    = $imageparts[1];
			// $imagebase64  = base64_decode($parts[1]);
			// $nameimg      = $cr_checkin . '-' . $std_checkin . '.png';
			// $file         = $uploadpath . $nameimg;

			// if (file_put_contents($file, $imagebase64)) {

			$checkin = $lms->insert('checkin', ["id_croom" => $cr_checkin, "id_sub" => $sub_checkin, "id_std" => $std_checkin, "ctime" => $time_checkin, "img_check" => $capimg]);

			if ($checkin) {

				//$resultck = $lms->select('student s JOIN checkin c  ON s.id = c.id_std', "std_id,prefix,fname,lname", "c.id_croom ='$cr_checkin'");

				$resultck = $lms->select('student s JOIN checkin c  ON s.id = c.id_std', "std_id,prefix,fname,lname", "c.id_croom ='$cr_checkin' AND s.id = '$std_checkin'");

				echo json_encode(array(
					'success' => 1,
					'resultck1' => $resultck[0]['std_id'],
					'resultck2' => $resultck[0]['prefix'],
					'resultck3' => $resultck[0]['fname'],
					'resultck4' => $resultck[0]['lname'],
				));
			} else {

				echo json_encode(array('success' => 2));
			}
		} else {
			echo json_encode(array('success' => 2));
		}
	} else {
		echo json_encode(array('success' => 2));
	}
}

if (isset($_POST['timestop']) && isset($_POST['thisid']) && isset($_POST['totaltime'])) {

	$timestop = $_POST['timestop'];
	$thisid = $_POST['thisid'];
	$totaltime = $_POST['totaltime'];

	$upcroom = $lms->update('classroom', ["etime" => $timestop, "totaltime" => $totaltime], "id='$thisid'");

	if ($upcroom) {
		echo json_encode(array('success' => 1));
	} else {
		echo json_encode(array('success' => 2));
	}
}

if (
	isset($_POST['cr_cap']) && isset($_POST['sub_cap']) &&
	isset($_POST['capimg5'])
) {
	$capimg5 = $_POST['capimg5'];
	$cr_cap = $_POST['cr_cap'];
	$sub_cap = $_POST['sub_cap'];

	$uploadpath   = '../upload/img_cap5min/';
	$parts        = explode(";base64,", $capimg5);
	$imageparts   = explode("image/", @$parts[0]);
	$imagetype    = $imageparts[1];
	$imagebase64  = base64_decode($parts[1]);
	$nameimg      = $sub_cap . '-' . $cr_cap . "-" . $namedate . '.png';
	$file         = $uploadpath . $nameimg;

	$checkimg = $lms->select('checkcap', "*", "path_cap='$nameimg'");

	if (!$checkimg) {

		if (file_put_contents($file, $imagebase64)) {

			$cap5min = $lms->insert('checkcap', ["id_sub" => $sub_cap, "id_croom" => $cr_cap, "time_cap" => $date, "path_cap" => $nameimg]);

			if ($cap5min) {

				echo json_encode(array(
					'success' => 1
				));
			} else {

				if (file_exists($file)) {
					unlink($file);
				}
				echo json_encode(array('success' => 2));
			}
		} else {
			echo json_encode(array('success' => 3));
		}
	} else {
		echo json_encode(array('success' => 4));
	}
}
