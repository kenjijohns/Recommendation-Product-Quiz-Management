<?php
    $pageTitle = "Quiz Result";
    include 'header.php';
    include 'db.php';
    session_start();

    if (!isset($_SESSION['selectedCategory']) || !isset($_SESSION['productTally'])) {
        header('Location: index.php');
        exit();
    }
    
    $selectedCategory = $_SESSION['selectedCategory'];
    $currentQuestion = $_SESSION['currentQuestion'];
    $productTally = $_SESSION['productTally'];

    $bonusQuestionQuery = "SELECT bqID, bqContent FROM bonus_question WHERE categoryID = '$selectedCategory'";
    $bonusQuestionResult = mysqli_query($conn, $bonusQuestionQuery);
    $bonusQuestion = mysqli_fetch_assoc($bonusQuestionResult);

    $maxTallyProducts = array();
    $maxTally = 0;

    foreach ($productTally as $product) {
        if ($product['tally'] == $maxTally) {
            $maxTallyProducts[] = $product;
        } elseif ($product['tally'] > $maxTally) {
            $maxTally = $product['tally'];
            $maxTallyProducts = array($product);
        }
    }
?>

<div class="container w-75 text-center mt-5">
    <h2 class="fw-bold">Thanks for waiting</h2>
    <p class="fs-3">Here are the products that might interest you.</p>
    <?php if (!empty($maxTallyProducts)): ?>
        <?php foreach ($maxTallyProducts as $maxTallyProduct): ?>
            <br>
            <div class="container w-100">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="card w-100">
                        <div class="card-body">
                            <?php
                            $prodID = $maxTallyProduct['id'];
                            $productDetailsQuery = "SELECT prodImage, prodURL, prodDescription FROM product WHERE prodID = '$prodID' AND categoryID = '$selectedCategory'";
                            $productDetailsResult = mysqli_query($conn, $productDetailsQuery);

                            if ($productDetailsRow  = mysqli_fetch_assoc($productDetailsResult)) {
                                $prodImage          = $productDetailsRow['prodImage'];
                                $prodURL            = $productDetailsRow['prodURL'];
                                $prodDescription    = $productDetailsRow['prodDescription'];

                                $_SESSION['prodID'] = $prodID;
                                ?>
                                <img src="management/<?php echo $prodImage; ?>" class="rounded img-thumbnail" style="width: 100%;" alt="Product Image" class="img-fluid">
                                <p class="fs-5"><?php echo $maxTallyProduct['name']; ?></p>
                                <textarea type="text" style="resize: none" class="form-control mt-3" rows="4" readonly><?php echo $prodDescription; ?></textarea>
                                <a href="<?php echo $prodURL; ?>" class="btn btn-primary rounded-pill mt-3" target="_blank">VIEW PRODUCT</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No favorable product found.</p>
    <?php endif; ?>

    <?php if ($bonusQuestion && isset($_SESSION['bonusAnswered'])): ?>
        <br>
        <?php
        if ($_SESSION['bonusAnswered']) {
            $voucherQuery = "SELECT voucherCode FROM voucher WHERE categoryID = '$selectedCategory'";
            $voucherResult = mysqli_query($conn, $voucherQuery);

            if ($voucherResult && $voucher = mysqli_fetch_assoc($voucherResult)['voucherCode']) {
                echo $voucher;
            } else {
                echo "Sorry, no voucher.";
            }
        } else {
            echo "Sorry, no voucher.";
        }
        session_unset();
        session_destroy(); 
        ?>
    <?php elseif ($bonusQuestion): ?>
        <br>
        <a class="btn btn-success rounded-pill" href="bonus.php">Get Voucher</a>
    <?php else: ?>
        <?php
        session_unset();
        session_destroy(); 
        ?>
    <?php endif; ?>
</div>