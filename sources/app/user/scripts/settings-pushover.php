<?php

$form_values = array(
    "token" => "",
    "user_key" => "",
);
$data_user = $userAuthed->getOption("notification.pushover");
if ($data_user && is_array($data_user)) {
    $form_values = array_merge($form_values, $data_user);
}

$errors = array();
$errorsTest = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["cancel-config"])) {
        header("LOCATION: ./?mod=user&a=settings");
        exit;
    }

    if (!empty($_POST["delete-config"])) {
        $userAuthed->mergeOptions(array(
            "notification" => array(
                "pushover" => false,
            ),
        ));
        $userStorage->save($userAuthed);
        $_SESSION["userSettingsSaved"] = true;
        header("LOCATION: ./?mod=user&a=settings");
        exit;
    }

    $form_values = array_intersect_key($_POST, $form_values);

    // Validation des champs
    foreach ($form_values AS $name => $value) {
        if (empty($value)) {
            $errors[$name] = "Ce champ doit être renseigné.";
        }
    }

    if (empty($errors)) {
        if (!empty($_POST["test-config"])) {
            $sender = \Message\AdapterFactory::factory("pushover", $form_values);
            try {
                $sender->send("La notification Pushover est fonctionnelle");
            } catch (Exception $e) {
                $errorsTest = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
            }
        } else {
            $userAuthed->mergeOptions(array(
                "notification" => array(
                    "pushover" => array(
                        "token" => $form_values["token"],
                        "user_key" => $form_values["user_key"],
                        "active" => true,
                    ),
                ),
            ));
            $userStorage->save($userAuthed);
            $_SESSION["userSettingsSaved"] = true;
            header("LOCATION: ./?mod=user&a=settings");
            exit;
        }
    }
}

