<!DOCTYPE html>
<html>
<head>
    <title>Ajo Invite</title>
</head>
<body>
    <h1>You are invited to join an Ajo</h1>
    <p>Hello,</p>
    <p>You have been invited to join the Ajo: <b>{{ $data['ajoName'] }}</b>. Please click the link below to accept the invite:</p>
    <p><a href="{{ url('/api/ajo-invites/accept/mail/' . $data['ajoId'] . '/' . $data['inviteEmail']) }}">Accept Invite</a></p>
    <p>Thank you!</p>
</body>
</html>
