<?php
include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";
?>
<div class="content">
    <div class="container d-flex justify-content-center align-items-center my-5">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="card bg-light shadow p-4">
                    <div class="text-center">
                        <h2 class="fw-bold mb-2">Berufsbereich erstellen</h2>
                        <p class="text-muted mb-4">Füge einen Berufsbereich hinzu</p>
                    </div>

                    <form method="post" action="../controllers/addJob.php">
                        <div class="form-floating w-100">
                            <label class="invisible" for="name">Füge einen Berufsbereich hinzu</label>
                            <input class="form-floating w-100" type="text" id="name" name="name" required placeholder="z.B. Deutsch, Mathematik..."/>
                        </div>
                        <button class="w-100 btn btn-primary btn-lg btn-block" type="submit">
                            Berufsbereich erstellen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
