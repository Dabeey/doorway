<x-mail::message>
    # 🏠 New Property Enquiry

    You have received a new enquiry for the property:
    **{{ $enquiry->property->title ?? 'N/A' }}**

    ---

    **From:** {{ $enquiry->name }}
    **Email:** {{ $enquiry->email }}

    @if($enquiry->phone)
        **Phone:** {{ $enquiry->phone }}
    @endif

    ---

    **Message:**

    > {{ $enquiry->message }}

    <x-mail::panel>
        **IP Address:** {{ $enquiry->ip_address ?? 'N/A' }}
        **User Agent:** {{ $enquiry->user_agent ?? 'N/A' }}
    </x-mail::panel>

    Thanks,<br>
    **{{ config('app.name') }}**
</x-mail::message>