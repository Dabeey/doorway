<?php

namespace App\Livewire;

use App\Models\Enquiry;
use Livewire\Component;
use App\Models\Property;
use App\Mail\PropertyInquiry;
use Illuminate\Support\Facades\Mail;

class ContactForm extends Component
{
    public Property $property;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $message = '';
    public $showSuccess = false;

    
    protected $rules = [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|max:20',
        'message' => 'required|min:10|max:1000',
    ];

    public function mount(Property $property)
    {
        $this->property = $property;
        $this->message = "Hi, I'm interested in the property '{$property->title}' listed at {$property->formatted_price}. Could you please provide more information?";
    }

    // Submit
    public function submit()
    {
        $this->validate();

        try {
            $enquiry = Enquiry::create([
                'property_id' => $this->property->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'message' => $this->message,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // Example: Send email to property contact or admin
            if ($this->property->contact_email) {
                Mail::to($this->property->contact_email)->queue(new PropertyInquiry($enquiry));
            }

            $this->showSuccess = true;
            $this->reset(['name', 'email', 'phone', 'message']);

            // Auto-hide success message after 5 seconds
            $this->dispatch('showSuccess');

        } catch (\Exception $e) {
            $this->addError('form', 'There was an error sending your message. Please try again.');
        }
    }

    public function hideSuccess()
    {
        $this->showSuccess = false;
    }
    public function render()
    {
        return view('livewire.contact-form');
    }
}