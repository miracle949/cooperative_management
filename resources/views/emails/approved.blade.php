<!DOCTYPE html>
<html>

<head>

    <style>
        ul {
            margin: 0 0 0 0;
            padding: 0;
        }
    </style>
</head>

<body>
    {{-- <h2>Congratulations, {{ $user->first_name }}!</h2>
    <p>Your membership in the cooperative has been <strong>approved</strong>.</p>
    <p>You can now log in to your account.</p> --}}

    {{-- <h2>Dear, {{ $user->first_name }}!</h2> --}}
    {{-- <p>Hi, {{ $user->first_name }}</p> --}}
    <h4>Hi, {{ $user->first_name }}</h4>

    <p>We are pleased to inform you that your application to become a member of Kingsland Pala-Pala Multi-Purpose
        Cooperative and Transport Services has been successfully <strong>approved.</strong></p>

    <p>As an official member, you now have access to the full range of cooperative services, including:</p>

    <ul style="margin: 0">
        <li>Savings programs designed to help you grow your funds securely</li>
        <li>Loan facilities tailored to support your personal or business needs</li>
        <li>Participation in cooperative decision-making and general assemblies</li>
        <li>Access to exclusive member benefits, updates, and announcements</li>
    </ul>

    <p>We encourage you to take full advantage of these services and actively engage with the cooperative community.
        Your membership marks the beginning of a partnership that supports your financial growth, security, and overall
        well-being.</p>

    <p>To complete your profile, please fill out the additional form below:</p>

    <a href="{{ url('/application-form/' . $user->id) }}">Fill up your information in this additional form</a>

    <p>Once again, welcome to the Kingsland Cooperative family! We are excited to have you on board and look forward to
        serving you.</p>

    <p class="mt-3">Sincerely,</p>

    <p>The Management Team
        Kingsland Pala-Pala Multi-Purpose Cooperative and Transport Services</p>

</body>

</html>