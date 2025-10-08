<?php
/**
 * Fallback Authentication Functions
 * Uses file-based storage when database is unavailable
 */

require_once __DIR__ . "/session.php";

// Try to include database config but don't fail if it doesn't work
@include_once __DIR__ . "/../config/database.php";

/**
 * Login user - fallback version
 */
function login_user($email, $password, $remember = false) {
    // First, try the database
    try {
        if (function_exists("getDB")) {
            $db = @getDB();
            if ($db) {
                $stmt = $db->prepare("
                    SELECT id, email, password, first_name, last_name, role, status
                    FROM users
                    WHERE email = ?
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user["password"])) {
                    if ($user["status"] !== "active") {
                        return ["success" => false, "message" => "Account is not active"];
                    }

                    set_user_session($user);

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60);
                        $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $stmt->execute([$token, $user["id"]]);
                        setcookie("remember_token", $token, $expires, "/", "", false, true);
                    }

                    return [
                        "success" => true,
                        "message" => "Login successful",
                        "user" => [
                            "id" => $user["id"],
                            "email" => $user["email"],
                            "name" => $user["first_name"] . " " . $user["last_name"],
                            "role" => $user["role"]
                        ]
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Database connection failed, using fallback: " . $e->getMessage());
    }

    // Fallback to file-based authentication
    $users_file = __DIR__ . "/../data/users.json";

    if (!file_exists($users_file)) {
        return ["success" => false, "message" => "Authentication system not configured"];
    }

    $users = json_decode(file_get_contents($users_file), true);

    foreach ($users as $user) {
        if ($user["email"] === $email) {
            if (password_verify($password, $user["password"])) {
                if (($user["status"] ?? "active") !== "active") {
                    return ["success" => false, "message" => "Account is not active"];
                }

                // Set session
                set_user_session($user);

                // Handle remember me with file storage
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60);

                    // Store token in user data
                    for ($i = 0; $i < count($users); $i++) {
                        if ($users[$i]["email"] === $email) {
                            $users[$i]["remember_token"] = $token;
                            file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
                            break;
                        }
                    }

                    setcookie("remember_token", $token, $expires, "/", "", false, true);
                }

                return [
                    "success" => true,
                    "message" => "Login successful",
                    "user" => [
                        "id" => $user["id"],
                        "email" => $user["email"],
                        "name" => $user["first_name"] . " " . $user["last_name"],
                        "role" => $user["role"]
                    ]
                ];
            } else {
                return ["success" => false, "message" => "Invalid email or password"];
            }
        }
    }

    return ["success" => false, "message" => "Invalid email or password"];
}

/**
 * Register user - fallback version
 */
function register_user($email, $password, $first_name, $last_name) {
    // Try database first
    try {
        if (function_exists("getDB")) {
            $db = @getDB();
            if ($db) {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->fetch()) {
                    return ["success" => false, "message" => "Email already registered"];
                }

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    INSERT INTO users (email, password, first_name, last_name, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $stmt->execute([$email, $hashed_password, $first_name, $last_name]);

                $user_id = $db->lastInsertId();

                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                set_user_session($user);

                return [
                    "success" => true,
                    "message" => "Registration successful",
                    "user_id" => $user_id
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Database registration failed, using fallback: " . $e->getMessage());
    }

    // Fallback to file-based registration
    $users_file = __DIR__ . "/../data/users.json";
    $users = [];

    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);

        // Check if email exists
        foreach ($users as $user) {
            if ($user["email"] === $email) {
                return ["success" => false, "message" => "Email already registered"];
            }
        }
    }

    // Create new user
    $new_user = [
        "id" => count($users) + 1,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_DEFAULT),
        "first_name" => $first_name,
        "last_name" => $last_name,
        "role" => "user",
        "status" => "active",
        "created_at" => date("Y-m-d H:i:s")
    ];

    $users[] = $new_user;

    // Save to file
    if (!is_dir(dirname($users_file))) {
        mkdir(dirname($users_file), 0755, true);
    }

    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

    set_user_session($new_user);

    return [
        "success" => true,
        "message" => "Registration successful",
        "user_id" => $new_user["id"]
    ];
}

/**
 * Logout user
 */
function logout_user() {
    // Clear remember token if exists
    if (isset($_COOKIE["remember_token"])) {
        // Try database first
        try {
            if (function_exists("getDB")) {
                $db = @getDB();
                if ($db) {
                    $stmt = $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                    $stmt->execute([get_user_id()]);
                }
            }
        } catch (Exception $e) {
            // Fallback: clear from file
            $users_file = __DIR__ . "/../data/users.json";
            if (file_exists($users_file)) {
                $users = json_decode(file_get_contents($users_file), true);
                for ($i = 0; $i < count($users); $i++) {
                    if ($users[$i]["id"] == get_user_id()) {
                        unset($users[$i]["remember_token"]);
                        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
                        break;
                    }
                }
            }
        }

        setcookie("remember_token", "", time() - 3600, "/", "", false, true);
    }

    clear_user_session();

    return ["success" => true, "message" => "Logged out successfully"];
}

/**
 * Get current user
 */
function get_current_user_info() {
    if (!is_logged_in()) {
        return null;
    }

    // Try database first
    try {
        if (function_exists("getDB")) {
            $db = @getDB();
            if ($db) {
                $stmt = $db->prepare("
                    SELECT id, email, first_name, last_name, role, status
                    FROM users
                    WHERE id = ?
                ");
                $stmt->execute([get_user_id()]);
                $user = $stmt->fetch();
                if ($user) {
                    return $user;
                }
            }
        }
    } catch (Exception $e) {
        // Continue with fallback
    }

    // Fallback to file
    $users_file = __DIR__ . "/../data/users.json";
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
        foreach ($users as $user) {
            if ($user["id"] == get_user_id()) {
                return $user;
            }
        }
    }

    return null;
}

/**
 * Check remember token
 */
function check_remember_token() {
    if (!isset($_COOKIE["remember_token"])) {
        return false;
    }

    $token = $_COOKIE["remember_token"];

    // Try database first
    try {
        if (function_exists("getDB")) {
            $db = @getDB();
            if ($db) {
                $stmt = $db->prepare("
                    SELECT id, email, first_name, last_name, role
                    FROM users
                    WHERE remember_token = ? AND status = 'active'
                ");
                $stmt->execute([$token]);
                $user = $stmt->fetch();

                if ($user) {
                    set_user_session($user);
                    return true;
                }
            }
        }
    } catch (Exception $e) {
        // Continue with fallback
    }

    // Fallback to file
    $users_file = __DIR__ . "/../data/users.json";
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
        foreach ($users as $user) {
            if (isset($user["remember_token"]) && $user["remember_token"] === $token) {
                if (($user["status"] ?? "active") === "active") {
                    set_user_session($user);
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * Validate password strength
 */
function validate_password($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    return true;
}
