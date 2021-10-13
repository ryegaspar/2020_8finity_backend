@component('mail::message')
You are invited to be an Admin for **{{ config('app.name') }} App**

Click the button below to create your login credentials

@component('mail::button', ['url' => $url])
    Register
@endcomponent

Thanks
@endcomponent
