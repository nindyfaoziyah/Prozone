# 1. multiplayer.php
$f="multiplayer.php"; $c=Get-Content $f -Raw; $c=$c.Replace("['sidebar-island.css', 'rpg-system.css', 'arena-v2.css', 'arena-battle-result.css']","['sidebar-island.css', 'dashboard-override.css', 'rpg-system.css', 'arena-v2.css', 'arena-battle-result.css']"); Set-Content $f $c -NoNewline; Write-Host "OK multiplayer"
# 2. ai-mentor.php
$f="ai-mentor.php"; $c=Get-Content $f -Raw; $c=$c.Replace("['pages/ai-mentor.css'];`$body_class       = getThemeClass();","['sidebar-island.css', 'dashboard-override.css', 'pages/ai-mentor.css'];`$body_class       = getThemeClass();"); Set-Content $f $c -NoNewline; Write-Host "OK ai-mentor"
# 3. clan.php - ganti semua manual CSS + body class
$f="clan.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","<link rel=""stylesheet"" href=""assets/css/ui-enhancements.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/dark-theme.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/glassmorphism.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK clan"
# 4. leaderboard.php
$f="leaderboard.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","<link rel=""stylesheet"" href=""assets/css/ui-enhancements.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/dark-theme.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/glassmorphism.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK leaderboard"
# 5. playground.php
$f="playground.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">","<link rel=""stylesheet"" href=""assets/css/ui-enhancements.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK playground"
# 6. friends.php
$f="friends.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","<link rel=""stylesheet"" href=""assets/css/ui-enhancements.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/dark-theme.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/glassmorphism.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK friends"
# 7. achievements.php
$f="achievements.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/dark-theme.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/glassmorphism.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK achievements"
# 8. certificates.php
$f="certificates.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","<link rel=""stylesheet"" href=""assets/css/sidebar-island.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","<link rel=""stylesheet"" href=""assets/css/dashboard-override.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","<link rel=""stylesheet"" href=""assets/css/ui-enhancements.css"">")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/dark-theme.css"">`r`n    <link rel=""stylesheet"" href=""assets/css/glassmorphism.css"">","")
$c=$c.Replace("<body>","<body class=""dashboard-layout"">")
Set-Content $f $c -NoNewline; Write-Host "OK certificates"
# 9. lesson.php
$f="lesson.php"; $c=Get-Content $f -Raw
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/style.css"">","")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/responsive.css"">","")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/global.css"">","")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/navbar.css"">","")
$c=$c.Replace("<link rel=""stylesheet"" href=""assets/css/dark-theme.css"">","")
$c=$c.Replace("<!-- Dashboard Layout -->`r`n    ","")
Set-Content $f $c -NoNewline; Write-Host "OK lesson"
# 10. profile.php - fix structural
$f="profile.php"; $c=Get-Content $f -Raw
$c=$c.Replace("</script>`r`n</body>`r`n    <?php require_once 'navbar.php'; ?>`r`n`r`n    <div class=""dashboard-main-container"">`r`n</html>","</script>`r`n</body>`r`n</html>")
$c=$c.Replace("page-profile""); ?>","dashboard-layout page-profile""); ?>")
Set-Content $f $c -NoNewline; Write-Host "OK profile"