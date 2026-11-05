<?php

namespace App\Helper\Trait;

trait HasAlert
{
    public function successAlert($message)
    {
        session()->flash('success', $message);
    }

    public function errorAlert($message)
    {
        session()->flash('error', $message);
    }

    public function warningAlert($message)
    {
        session()->flash('warning', $message);
    }

    public function createdAlert()
    {
        session()->flash('success', 'Data Successfully Created!');
    }

    public function deletedAlert()
    {
        session()->flash('success', 'Data Successfully Deleted!');
    }

    public function updatedAlert()
    {
        session()->flash('success', 'Data Successfully Updated!');
    }
}
