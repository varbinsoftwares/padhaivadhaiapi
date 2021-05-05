
<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>assets/assets/images/favicon.png">
    <title>AdminBite admin Template - The Ultimate Multipurpose admin template</title>
    <!-- Custom CSS -->
    <link href="<?php echo base_url(); ?>assets/dist/css/style.min.css" rel="stylesheet">
     <link href="<?php echo base_url(); ?>assets/gritter/css/jquery.gritter.css" rel="stylesheet" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url(<?php echo base_url(); ?>assets/assets/images/big/auth-bg.jpg) no-repeat center center;">
            <div class="auth-box">
                <div id="loginform">
                    <div class="logo">
                        <h5 class="font-medium m-b-20">Sign In to Admin</h5>
                    </div>
                    <!-- Form -->
                    <div class="row">
                        <div class="col-12">
                            <form class="form-horizontal m-t-20"  method="post" id="loginform" action="">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="ti-user"></i></span>
                                    </div>
                                    <input type="email" class="form-control form-control-lg" name="email"  placeholder="Username" aria-label="Username/Email" aria-describedby="basic-addon1">
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class="ti-pencil"></i></span>
                                    </div>
                                    <input type="password" class="form-control form-control-lg" placeholder="Password" aria-label="Password" name="password" aria-describedby="basic-addon1">
                                </div>
                              
                                <div class="form-group text-center">
                                    <div class="col-xs-12 p-b-20">
                                        <button class="btn btn-block btn-lg btn-info"  name="signIn" value="signIn" type="submit">Log In</button>
                                    </div>
                                </div>
                              
                             
                            </form>
                        </div>
                    </div>
                </div>
             
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="<?php echo base_url(); ?>assets/assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="<?php echo base_url(); ?>assets/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- ============================================================== -->
    <script src="<?php echo base_url(); ?>assets/gritter/js/jquery.gritter.js"></script>

    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>
    $('[data-toggle="tooltip"]').tooltip();
    $(".preloader").fadeOut();
    // ============================================================== 
    // Login and Recover Password 
    // ============================================================== 
    $('#to-recover').on("click", function() {
//        $("#loginform").slideUp();
//        $("#recoverform").fadeIn();
    });
    </script>
    <script>
    $(function () {
        <?php
        $checklogin = $this->session->flashdata('checklogin');
        if($checklogin['show']){
        ?>
        $.gritter.add({
            title: '<?php echo $checklogin['title']; ?>',
            text: '<?php echo $checklogin['text']; ?>',
            image: '<?php echo base_url(); ?>assets/emoji/sad.png',
            sticky: true,
            time: '',
            class_name: 'my-sticky-class'
        });
       <?php
        }
       ?>
    })
</script>
</body>

</html>