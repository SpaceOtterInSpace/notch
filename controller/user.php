<?php
use Psecio\Gatekeeper\Gatekeeper;

/**
 * User routing
 */
$app->group('/user', function() use ($app, $di) {

    // ------ Login/Logout ------
    $app->get('/login', function() use ($app, $di) {
        $app->render('user/login.php');
    });
    $app->post('/login', function() use ($app, $di) {
        $message = 'Login successful!';

        $username = $app->request->post('username');
        $password = $app->request->post('password');

        $credentials = array(
            'username' => $username,
            'password' => $password
        );
        $success = Gatekeeper::authenticate($credentials);

        if ($success === false) {
            $message = 'There was an error logging in!';
        } else {
            $userData = Gatekeeper::findUserByUsername($username);
            $_SESSION['username'] = $userData->username;
            $_SESSION['userId'] = $userData->id;
        }

        $data = array(
            'success' => $success,
            'message' => $message
        );

        $app->render('user/login.php', $data);
    });
    $app->get('/logout', function() use ($app, $di) {
        unset($_SESSION['username']);
        $app->render('user/logout.php');
    });

    // ------ Register ------
    $app->get('/register', function() use ($app, $di) {
        $app->render('user/register.php');
    });
    $app->post('/register', function() use ($app, $di) {
        $message = 'Success!';
        $posted = $app->request->post();

        $credentials = array(
            'username' => $posted['username'],
            'password' => $posted['password'],
            'email' => $posted['email']
        );

        try {
            $user = Gatekeeper::findUserByUsername($posted['username']);
            $message = 'User "'.$posted['username'].'" already exists!';
            $success = false;
        } catch (\Exception $e) {
            // Not found - register!
            $success = Gatekeeper::register($credentials);
            if ($success === false) {
                $message = 'There was an error creating the user!';
            }
        }

        $data = array(
            'success' => $success,
            'message' => $message
        );
        $app->render('user/register.php', $data);
    });

    // ------ Detail ------
    $app->get('/detail/:username', function($username) use ($app, $di) {
        $user = new Notch\Users($di);
        $data = array(
            'user' => $user->getUserByUsername($username),
            'currentUser' => $_SESSION['username']
        );
        $app->render('user/detail.php', $data);
    });

    // ------ Edit ------
    $app->get('/edit/:username', function($username) use ($app, $di) {
        $user = new Notch\Users($di);
        $data = array(
            'user' => $user->getUserByUsername($username),
            'currentUser' => $_SESSION['username']
        );
        $app->render('user/edit.php', $data);
    });
    $app->post('/edit/:username', function($username) use ($app, $di) {
        $success = true;
        $message = 'User updated successfully!';
        $user = new Notch\Users($di);
        $userData = $user->getUserByUsername($username);

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $destination = realpath(__DIR__.'/../assets/img/uploads').'/'.$_FILES['avatar']['name'];
            move_uploaded_file($_FILES['avatar']['tmp_name'], $destination);
        } else {
            $success = false;
            $message = 'Problem uploading avatar image!';
        }

        if ($success == true) {
            $data = $app->request->post();
            $data['id'] = $userData['id'];
            $data['avatar'] = $_FILES['avatar']['name'];
            $user->save($data);
        }

        $data = array(
            'user' => $userData,
            'currentUser' => $_SESSION['username'],
            'success' => $success,
            'message' => $message
        );
        $app->render('/user/edit.php', $data);
    });

    // ------ Delete ------
    $app->get('/delete/:username', function($username) use ($app, $di) {
        // Be sure the current user is in the admin group
        $user = Gatekeeper::findUserById($_SESSION['userId']);
        if ($user->inGroup('3') === false) {
            $app->redirect('/error');
        }

        $user = new Notch\Users($di);
        $userData = $user->getUserByUsername($username);

        $data = array(
            'user' => $userData
        );
        $app->render('/user/delete.php', $data);
    });
    $app->post('/delete/:username', function($username) use ($app, $di) {
        if ($user->inGroup('3') === false) {
            $app->redirect('/error');
        }

        $message = 'There was an error deleteing user '.$username;
        $user = new Notch\Users($di);
        $userData = $user->getUserByUsername($username);

        $success = $user->delete($userData['id']);
        if ($success === true) {
            $message = 'User '.$username.' deleted successfully';
        }

        $data = array(
            'user' => $userData,
            'success' => $success,
            'message' => $message
        );
        $app->render('/user/delete.php', $data);
    });
});
?>