<?php

namespace App\Http\Controllers;

class DemoErrorController extends Controller
{
    /**
     * Display error gallery for video recording/documentation
     */
    public function index()
    {
        $errors = [
            [
                'id' => 'db-connection',
                'title' => 'Database Connection Timeout',
                'severity' => 'critical',
                'type' => 'Database Error',
                'message' => 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away',
                'description' => 'Database connection lost. The connection to the database server was unexpectedly closed, possibly due to network timeout or server restart.',
                'stack' => [
                    'at PDOStatement->execute() in Connection.php:374',
                    'at Connection->statement() in F01JawabanController.php:82',
                    'at F01JawabanController->store() in helpers.php:241',
                    'at call_user_func_array() in Kernel.php:193',
                ],
                'code' => 2006,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
            ],
            [
                'id' => 'validation-error',
                'title' => 'Validation Failed - Invalid Input Data',
                'severity' => 'warning',
                'type' => 'Validation Error',
                'message' => 'The given data was invalid.',
                'description' => 'Multiple validation rules failed. Check the error details below for which fields require correction.',
                'details' => [
                    'email' => 'The email field must be a valid email address.',
                    'phone' => 'The phone field must be at least 10 characters.',
                    'file_bukti' => 'The uploaded file exceeds the maximum size of 5MB.',
                    'jawaban' => 'The jawaban field is required when aspek_id is provided.',
                ],
                'code' => 422,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
            ],
            [
                'id' => 'permission-denied',
                'title' => 'Authorization Failed - Access Denied',
                'severity' => 'error',
                'type' => 'Permission Error',
                'message' => 'This action is unauthorized.',
                'description' => 'You do not have permission to access this resource. Only superadmin users can perform this action.',
                'stack' => [
                    'at UserUppPolicy->update() in AuthorizesRequests.php:124',
                    'at UserUppController->update() in Kernel.php:176',
                ],
                'code' => 403,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
                'current_user' => 'user@example.com',
                'required_role' => 'Superadmin',
            ],
            [
                'id' => 'data-inconsistency',
                'title' => 'Data Inconsistency - Corrupted Records',
                'severity' => 'critical',
                'type' => 'Data Integrity Error',
                'message' => 'Foreign key constraint violation detected.',
                'description' => 'Referenced data no longer exists or is corrupted. F01 Jawaban records reference non-existent F01 Pertanyaan entries.',
                'stack' => [
                    'CONSTRAINT `fk_f01_jawaban_pertanyaan` FOREIGN KEY (`f01_pertanyaan_id`) REFERENCES `f01_pertanyaan` (`id`)',
                    'Affected records: 234 jawaban entries pointing to deleted pertanyaan',
                ],
                'affectedTable' => 'f01_jawaban',
                'recordCount' => 234,
                'code' => 1451,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
            ],
            [
                'id' => 'resource-not-found',
                'title' => 'Resource Not Found',
                'severity' => 'warning',
                'type' => 'HTTP Error',
                'message' => 'No query results found for model [App\\Models\\UserUpp].',
                'description' => 'The requested resource does not exist or has been deleted. Attempted to access UPP ID 999999 which is not in the database.',
                'stack' => [
                    'at Builder->firstOrFail() in UppController.php:47',
                    'at UppController->show() in Kernel.php:176',
                ],
                'code' => 404,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
                'requested' => '/upps/999999',
            ],
            [
                'id' => 'api-timeout',
                'title' => 'Request Timeout - External Service Call Failed',
                'severity' => 'error',
                'type' => 'Timeout Error',
                'message' => 'cURL error 28: Operation timed out after 30000 milliseconds',
                'description' => 'The API request to SSO server took too long and was cancelled. This could indicate network issues or the external service is overloaded.',
                'stack' => [
                    'at Client->request() in SsoLoginController.php:34',
                    'at SsoLoginController->getAccessToken() in SsoLoginController.php:89',
                    'at SsoLoginController->callback() in Kernel.php:176',
                ],
                'service' => 'SSO Auth Server',
                'timeout' => '30 seconds',
                'code' => 28,
                'timestamp' => 'Fri Mar 09 2026 14:32:45 GMT+0700',
            ],
        ];

        return view('demo.errors', [
            'errors' => $errors,
            'totalErrors' => count($errors),
        ]);
    }
}
