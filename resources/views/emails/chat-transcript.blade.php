<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Chat — {{ $projectName }}</title>
<style>
  body { margin: 0; padding: 0; background: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
  .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
  .header { background: #1E1E1E; color: #ffffff; padding: 24px 32px; text-align: center; }
  .header h1 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
  .header p { margin: 8px 0 0; font-size: 13px; color: #a0a0a0; }
  .body { padding: 24px 32px; }
  .intro { font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.6; }
  .msg { margin-bottom: 12px; display: flex; }
  .msg-visitor { justify-content: flex-end; }
  .msg-agent, .msg-bot, .msg-system { justify-content: flex-start; }
  .bubble { max-width: 80%; padding: 10px 14px; border-radius: 12px; font-size: 14px; line-height: 1.5; }
  .bubble-visitor { background: #1E1E1E; color: #ffffff; border-bottom-right-radius: 4px; }
  .bubble-agent { background: #f0f0f3; color: #1a1a1a; border-bottom-left-radius: 4px; }
  .bubble-bot { background: #e8f5e9; color: #1a1a1a; border-bottom-left-radius: 4px; }
  .bubble-system { background: #fff3e0; color: #6d4c00; border-radius: 8px; font-size: 12px; text-align: center; width: 100%; }
  .sender { font-size: 11px; font-weight: 600; color: #888; margin-bottom: 3px; }
  .time { font-size: 10px; color: #aaa; margin-top: 3px; }
  .footer { padding: 20px 32px; text-align: center; border-top: 1px solid #eee; }
  .footer p { margin: 0; font-size: 12px; color: #999; }
</style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f7; padding: 32px 0;">
<tr><td align="center">
<div class="container">
  <div class="header">
    <h1>{{ $projectName }}</h1>
    <p>Riwayat Percakapan Chat</p>
  </div>

  <div class="body">
    <p class="intro">
      Halo <strong>{{ $visitorName }}</strong>,<br>
      Berikut adalah riwayat lengkap percakapan Anda. Terima kasih telah menghubungi kami!
    </p>

    @foreach($messages as $msg)
      <div style="margin-bottom: 12px;">
        <div style="font-size: 11px; font-weight: 600; color: #888; margin-bottom: 3px;
          @if($msg->sender_type === 'visitor') text-align: right; @endif">
          {{ $msg->sender_name }}
          @if($msg->sender_type === 'bot') 🤖 @endif
        </div>
        <div style="
          padding: 10px 14px;
          border-radius: 12px;
          font-size: 14px;
          line-height: 1.5;
          max-width: 80%;
          @if($msg->sender_type === 'visitor')
            background: #1E1E1E; color: #ffffff; border-bottom-right-radius: 4px; margin-left: auto;
          @elseif($msg->sender_type === 'bot')
            background: #e8f5e9; color: #1a1a1a; border-bottom-left-radius: 4px;
          @elseif($msg->sender_type === 'system')
            background: #fff3e0; color: #6d4c00; border-radius: 8px; font-size: 12px; text-align: center; margin: 0 auto;
          @else
            background: #f0f0f3; color: #1a1a1a; border-bottom-left-radius: 4px;
          @endif
        ">
          {!! nl2br(e($msg->content)) !!}
        </div>
        <div style="font-size: 10px; color: #aaa; margin-top: 3px;
          @if($msg->sender_type === 'visitor') text-align: right; @endif">
          {{ $msg->created_at ? $msg->created_at->format('d M Y H:i') : '' }}
        </div>
      </div>
    @endforeach
  </div>

  <div class="footer">
    <p>Powered by <strong>BeanTalk</strong> — Universal Customer Chat</p>
  </div>
</div>
</td></tr>
</table>
</body>
</html>
