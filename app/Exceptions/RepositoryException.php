<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

/**
 * Class RepositoryException.
 */
class RepositoryException extends Exception
{
    public static function createFailed()
    {
        return new static(__('exceptions.actions.create_failed'));
    }

    public static function updateFailed()
    {
        return new static(__('exceptions.actions.update_failed'));
    }

    public static function deleteFailed()
    {
        return new static(__('exceptions.actions.delete_failed'));
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return RedirectResponse
     */
    public function render()
    {
        //return redirect()->back()->withInput();
    }
}
