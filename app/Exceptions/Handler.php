<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

class Handler extends ExceptionHandler
{

    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function render($request, Throwable $e)
    {

        if ($e instanceof ValidationException) {

            $errors = $e->validator->errors()->getMessages();

            return $this->errorResponse($errors, 422);
        }

        if ($e instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($e->getModel()));

            return $this->errorResponse("{$modelName} with this identifiaction does not exists", 404);
        }

        if ($e instanceof AuthenticationException) {
            return $this->errorResponse('Unauthenticated!', 403);
        }

        if ($e instanceof AuthorizationException) {
            return $this->errorResponse($e->getMessage(), 401);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->errorResponse('The specified URL cannot be found!', 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The specified method for this request is not valid!', 405);
        }

        if ($e instanceof HttpException) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }

        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1451) {
                return $this->errorResponse('Cannot remove this resource permanently!', 409);
            } else {
                return $this->errorResponse('Error with the database. Try again', 422);
            }
        }
        return $this->errorResponse('Unexpected error! Try again later.', 500);
    }
}
