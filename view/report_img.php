<?php

if (!isset($_SESSION['id_teacher'])) {

    $_SESSION['error'] = "กรุณาเข้าสู่ระบบใหม่อีกครั้ง!";
    echo "<script>window.location.href='auth/login.php';</script>";
    exit;
}

if (isset($_GET['repid']) && isset($_GET['subid'])) {

    $repid = $_GET['repid'];
    $subid = $_GET['subid'];

} else {

    $_SESSION['error'] = "เกิดข้อผิดพลาด! ไม่พบข้อมูล!";
    echo "<script> window.history.back()</script>";
    exit;
}

if (isset($_GET['delete_repimg'])) {

    $delete_repimg = $_GET['delete_repimg'];
    $oldpath = $lms->select('checkcap', 'path_cap', "id='$delete_repimg'");
    $delpath = $oldpath[0]['path_cap'];
    $del_repimg = $lms->delete('checkcap', "id='$delete_repimg'");

    if (!empty($del_repimg)) {

        if (file_exists('upload/img_cap5min/' . $delpath)) {
            unlink('upload/img_cap5min/' . $delpath);
        }
        $_SESSION['success'] = "นำภาพออกสำเร็จ!";
        echo "<script>window.history.back();</script>";
        exit;
    } else {

        whenerror();
        exit;
    }
}

function paginationimg($table,$rows="*",$where = null,$page_rows=10,$repid,$subid){

    $lms = new lms();
		
    $countrow=$lms->select($table,'COUNT(id)',$where);
    $last = ceil($countrow[0]['COUNT(id)']/$page_rows);
    
    if($last < 1){
        $last = 1;
    }
    
    $pagenum = 1;

    if(isset($_GET['pn'])){
        $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
    }

    if ($pagenum < 1) {
        $pagenum = 1;
    }
    else if ($pagenum > $last) {
        $pagenum = $last;
    }

    $limit = ' LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;
    $searchx = $where.$limit;
    $result = $lms->select($table,$rows,$searchx);

    $paginationCtrls = '';

    if($last != 1){
        
        $paginationCtrls .= '<nav aria-label="Page navigation"><ul class="pagination">';

        if ($pagenum > 1) {
            $previous = $pagenum - 1;
            $paginationCtrls .= '<li class="page-item"><a href="'.$_SERVER['PHP_SELF'].'?pn='.$previous.'&page=report_img&repid='.$repid.'&subid='.$subid.'" class="page-link">Previous</a></li>';
     
            for($i = $pagenum-4; $i < $pagenum; $i++){
                if($i > 0){
                    $paginationCtrls .= '<li class="page-item"><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&page=report_img&repid='.$repid.'&subid='.$subid.'" class="page-link">'.$i.'</a></li>';
                }
            }
        }
     
        $paginationCtrls .= '<li class="page-item"><a class="page-link active" aria-current="page">'.$pagenum.'</a></li>';
     
        for($i = $pagenum+1; $i <= $last; $i++){
            $paginationCtrls .= '<li class="page-item"><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&page=report_img&repid='.$repid.'&subid='.$subid.'" class="page-link">'.$i.'</a></li>';
            if($i >= $pagenum+4){
                break;
            }
        }
        if ($pagenum != $last) {
            $next = $pagenum + 1;
            $paginationCtrls .= '<li class="page-item"><a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'&page=report_img&repid='.$repid.'&subid='.$subid.'" class="page-link">Next</a></li>';
        }
        
        $paginationCtrls .='</ul></nav>';
    }
    
    return array($result,$paginationCtrls);
}
?>
<div class="album py-5 " style="background-color:#f0f8ff;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="?page=report.php">รายงาน</a></li>
                <li class="breadcrumb-item active" aria-current="page">รูปภาพรายงาน</li>
            </ol>
        </nav>
        <div class="row mb-4 d-flex justify-content-center">
            <div class="col-sm-5">
                <h2>รายงานภาพการเข้าเรียน</h2>
            </div>
            <div class="col-sm-5 text-end">
                <a class="btn btn-primary" href="?page=report&subid=<?= $subid ?>"><i class="fa-regular fa-circle-left"></i>&nbsp;กลับ</a>
            </div>
        </div>
        <div class="px-5 py-4 bg-light rounded-5 shadow-lg">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-box clearfix">
                        <div class=" row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                            <?php
                            $repimg_page = paginationimg('checkcap', '*', "id_croom='$repid'", 6,$repid,$subid);
                            foreach ($repimg_page[0] as $repimg_list) {
                            ?>
                                <div class="col">
                                    <div class="card shadow-sm" style="overflow:hidden;">
                                        <div width="400" height="300" style="overflow:hidden;">
                                            <img src="upload/img_cap5min/<?= $repimg_list['path_cap'] ?>" style="width: 400px; height: 300px; ">
                                        </div>
                                        <div class="card-body" style="height: 90px;">
                                            <h6 class="card-text fw-bold" style="height:20px;white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <?= $repimg_list['time_cap']; ?>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a type="button" class="btn btn-sm btn-danger px-2 delete_checkcap" id="<?= $repimg_list['id'] ?>" data-name-img="<?= $repimg_list['path_cap'] ?>">delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-12 pt-5 d-flex justify-content-end">
                            <?php echo $repimg_page[1]; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('click', '.delete_checkcap', function() {
        var id = $(this).attr("id");
        var name_img = $(this).attr("data-name-img");
        swal.fire({
            title: 'ต้องการนำภาพนี้ออก !',
            imageUrl: 'upload/img_cap5min/' + name_img,
            imageWidth: 500,
            imageHeight: 250,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'yes!',
            cancelButtonText: 'no'
        }).then((result) => {
            if (result.value) {
                window.location.href = "?page=report_img&delete_repimg=" + id + "&repid=<?= $repid ?>" + "&subid=<?= $subid ?>";
            }
        });
    });
</script>
<style>
    .swal2-image{
        margin-left: 90px;
    }
</style>
<?php include('view/student_view.php') ?>