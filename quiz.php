<style>
    .conditional-answer-btn.selected,
    .parent-answer-btn.selected {
        background-color: green;
    }
</style>

<?php
    $pageTitle = "Quiz";
    include 'header.php';
    include 'db.php';
    session_start();

    $selectedCategory = $_SESSION['selectedCategory'];
    if (!isset($_SESSION['selectedCategory'])) {
        header('Location: index.php');
        exit();
    }

    if (!isset($_SESSION['currentQuestion'])) {
        $_SESSION['currentQuestion'] = 1;
    }

    $selectedAnswer = '';
    if (!isset($_SESSION['selectedAnswers'])) {
        $_SESSION['selectedAnswers'] = array();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $selectedAnswer = $_POST['selectedAnswer'];
        $_SESSION['selectedAnswers'][$_SESSION['currentQuestion']] = $selectedAnswer;
        $conditionalQuery = "SELECT cq.cqID, cq.cqContent, cq.cqMaxAnswer
                            FROM conditional_question cq
                            LEFT JOIN trigger_condition tc ON cq.cqID = tc.cqID
                            WHERE tc.answerID = '$selectedAnswer'";
        $conditionalResult = mysqli_query($conn, $conditionalQuery);

        if ($conditionalResult && mysqli_num_rows($conditionalResult) > 0) {
            $conditionalRow = mysqli_fetch_assoc($conditionalResult);
            $cqID           = $conditionalRow['cqID'];
            $cqContent      = $conditionalRow['cqContent'];
            $cqMaxAnswer    = $conditionalRow['cqMaxAnswer'];
            ?>

            <div class="container w-50 my-5 text-center">
                <form action="quiz.php" method="post" id="quizForm">
                    <h2><?php echo $cqContent; ?></h2>
                    <?php
                    $conditionalAnswerQuery = "SELECT a.answerID, a.answerContent
                                                FROM answer a
                                                LEFT JOIN question_answer qa ON qa.answerID = a.answerID
                                                WHERE qa.cqID = '$cqID'";
                    $conditionalAnswerResult = mysqli_query($conn, $conditionalAnswerQuery);

                    while ($conditionalAnswerRow = mysqli_fetch_assoc($conditionalAnswerResult)) {
                        $conditionalAnswerID = $conditionalAnswerRow['answerID'];
                        $conditionalAnswerContent = $conditionalAnswerRow['answerContent'];
                        echo "<button type='button' class='conditional-answer-btn btn btn-primary mb-2 rounded-pill w-100' data-answer-id='$conditionalAnswerID' onclick='selectAnswer($conditionalAnswerID)'>$conditionalAnswerContent</button><br>";
                    }
                    ?>
                    <button type="submit" class="btn btn-primary" id="nextButton" disabled="disabled">Next</button>
                    <input type="hidden" name="selectedAnswer" id="selectedAnswer" value="">
                </form>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
            <script>
                var selectedAnswer = '';

                $(document).ready(function() {
                    $(document).on('click', '.conditional-answer-btn', function() {
                        selectedAnswer = $(this).data('answer-id');
                        $('.conditional-answer-btn').removeClass('selected');
                        $(this).addClass('selected');
                        $('#nextButton').prop('disabled', false);
                    });

                    $('#nextButton').on('click', function() {
                        $('#selectedAnswer').val(selectedAnswer);
                        $('#quizForm').submit();
                    });
                });
            </script>
            <?php
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['currentQuestion']++;
    }

    $query = "SELECT pqID, pqContent, pqMaxAnswer
              FROM parent_question 
              WHERE pqOrder = {$_SESSION['currentQuestion']} AND categoryID = '$selectedCategory'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row           = mysqli_fetch_assoc($result);
        $pqID          = $row['pqID'];
        $pqContent     = $row['pqContent'];
        $pqMaxAnswer   = $row['pqMaxAnswer'];

        $answerQuery = "SELECT a.answerID, a.answerContent
                        FROM answer a
                        LEFT JOIN question_answer qa ON qa.answerID = a.answerID
                        WHERE qa.pqID = '$pqID' ";
        $answerResult = mysqli_query($conn, $answerQuery);
        ?>
        <div class="container w-50 my-5 text-center">
            <form action="quiz.php" method="post" id="quizForm">
                <h2><?php echo $pqContent; ?></h2>
                <?php
                while ($answerRow = mysqli_fetch_assoc($answerResult)) {
                    $answerID = $answerRow['answerID'];
                    $answerContent = $answerRow['answerContent'];
                    echo "<button type='button' class='parent-answer-btn btn btn-primary mb-2 rounded-pill w-100' data-answer-id='$answerID' onclick='selectAnswer($answerID)'>$answerContent</button><br>";
                }
                ?>
                <button type="submit" class="btn btn-primary" id="nextButton" disabled="disabled">Next</button>
                <input type="hidden" name="selectedAnswer" id="selectedAnswer" value="">
            </form>
        </div>
        <?php
    } else {
        $selectedAnswers = $_SESSION['selectedAnswers'];
        $productTally = array();

        foreach ($selectedAnswers as $answerID) {
            $productQuery = "SELECT p.prodID, p.prodName
                            FROM product p
                            INNER JOIN product_answer pa ON p.prodID = pa.prodID
                            WHERE pa.answerID = '$answerID'";

            $productResult = mysqli_query($conn, $productQuery);

            while ($productRow = mysqli_fetch_assoc($productResult)) {
                $prodID   = $productRow['prodID'];
                $prodName = $productRow['prodName'];

                if (!isset($productTally[$prodID])) {
                    $productTally[$prodID] = array(
                        'id'    => $prodID,
                        'name'  => $prodName,
                        'tally' => 1,
                    );
                } else {
                    $productTally[$prodID]['tally']++;
                }
            }
        }

        $_SESSION['productTally'] = $productTally;

        header("Location: result.php");
        exit;
    }
?>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    var selectedAnswer = '';

    $(document).ready(function() {
        $(document).on('click', '.parent-answer-btn', function() {
            selectedAnswer = $(this).data('answer-id');
            $('.parent-answer-btn').removeClass('selected');
            $(this).addClass('selected');
            $('#nextButton').prop('disabled', false);
        });

        $('#nextButton').on('click', function() {
            $('#selectedAnswer').val(selectedAnswer);
            $('#quizForm').submit();
        });
    });
</script>
