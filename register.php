<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$force_theme      = 'light';
$page_title       = 'Daftar';
$page_description = 'Daftar di ' . APP_NAME . ' - Platform pembelajaran coding interaktif';
$page_css         = ['components/button.css', 'components/card.css', 'components/form.css', 'components/alert.css', 'components/badge.css', 'components/auth.css'];
$body_class       = getThemeClass();

$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['register_old'] ?? [];
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_errors'], $_SESSION['register_old'], $_SESSION['register-success']);

// Compute strength for repopulation (server has no zxcvbn)
$password_value = $old['password'] ?? '';
$hasMinLength = strlen($password_value) >= 8;
$hasUpperLower = preg_match('/[A-Z]/', $password_value) && preg_match('/[a-z]/', $password_value);
$hasNumber = preg_match('/\d/', $password_value);
$hasSpecial = preg_match('/[^A-Za-z0-9]/', $password_value);
$strengthScore = (int)$hasMinLength + (int)$hasUpperLower + (int)$hasNumber + (int)$hasSpecial;
$strengthPercent = ($strengthScore / 4) * 100;
$strengthLabel = ['Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'][$strengthScore] ?? 'Lemah';
$strengthClass = ['weak', 'weak', 'fair', 'good', 'strong'][$strengthScore] ?? 'weak';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>

    <!-- SVG Symbol Definitions -->
    <svg style="display: none;" aria-hidden="true">
        <defs>
            <linearGradient id="brandGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#3b82f6"/>
                <stop offset="100%" stop-color="#0ea5e9"/>
            </linearGradient>
        </defs>
        <symbol id="brandLogo" viewBox="0 0 100 100">
            <path d="M 25 20 L 25 75 Q 25 80 30 80 L 35 80 Q 40 80 40 75 L 40 20 Q 40 15 35 15 L 30 15 Q 25 15 25 20 Z" fill="url(#brandGrad)"/>
            <path d="M 40 20 Q 40 15 45 15 L 60 15 Q 70 15 70 25 L 70 35 Q 70 45 60 45 L 45 45 Q 40 45 40 40 L 40 30 Q 40 25 45 25 L 60 25 Q 65 25 65 30 L 65 35 Q 65 40 60 40 L 45 40 Q 40 40 40 35 Z" fill="url(#brandGrad)"/>
        </symbol>
        <!-- Tech Logos (Official Simple Icons) -->
        <symbol id="logo-js" viewBox="0 0 24 24"><path fill="#F7DF1E" d="M0 0h24v24H0V0zm22.034 18.276c-.175-1.095-.888-2.015-3.003-2.873-.736-.345-1.554-.585-1.797-1.14-.091-.33-.105-.51-.046-.705.15-.646.915-.84 1.515-.66.39.12.75.42.976.9 1.034-.676 1.034-.676 1.755-1.125-.27-.42-.404-.601-.586-.78-.63-.705-1.469-1.065-2.834-1.034l-.705.089c-.676.165-1.32.525-1.71 1.005-1.14 1.291-.811 3.541.569 4.471 1.365 1.02 3.361 1.244 3.616 2.205.24 1.17-.87 1.545-1.966 1.41-.811-.18-1.26-.586-1.755-1.336l-1.83 1.051c.21.48.45.689.81 1.109 1.74 1.756 6.09 1.666 6.871-1.004.029-.09.24-.705.074-1.65l.046.067zm-8.983-7.245h-2.248c0 1.938-.009 3.864-.009 5.805 0 1.232.063 2.363-.138 2.711-.33.689-1.18.601-1.566.48-.396-.196-.597-.466-.83-.855-.063-.105-.11-.196-.127-.196l-1.825 1.125c.305.63.75 1.172 1.324 1.517.855.51 2.004.675 3.207.405.783-.226 1.458-.691 1.811-1.411.51-.93.402-2.07.397-3.346.012-2.054 0-4.109 0-6.179l.004-.056z"/></symbol>
        <symbol id="logo-php" viewBox="0 0 24 24"><path fill="#777BB4" d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 0 1-.305.847c-.143.255-.33.49-.561.703zm4.024.715l.543-2.799c.063-.318.039-.536-.068-.651-.107-.116-.336-.174-.687-.174H11.46l-.704 3.625H9.388l1.23-6.327h1.367l-.327 1.682h1.218c.767 0 1.295.134 1.586.401s.378.7.263 1.299l-.572 2.944h-1.389zm7.597-2.265a2.782 2.782 0 0 1-.305.847c-.143.255-.33.49-.561.703a2.44 2.44 0 0 1-.917.551c-.336.108-.765.164-1.286.164h-1.18l-.327 1.682h-1.378l1.23-6.326h2.649c.797 0 1.378.209 1.744.628.366.417.477 1.001.331 1.751zM17.766 10.207h-.943l-.516 2.648h.838c.557 0 .971-.105 1.242-.314.272-.21.455-.559.551-1.049.092-.47.049-.802-.125-.995s-.524-.29-1.047-.29z"/></symbol>
        <symbol id="logo-css3" viewBox="0 0 24 24"><path fill="#1572B6" d="M1.5 0h21l-1.91 21.563L11.977 24l-8.565-2.438L1.5 0zm17.09 4.413L5.41 4.41l.213 2.622 10.125.002-.255 2.716h-6.64l.24 2.573h6.182l-.366 3.523-2.91.804-2.956-.81-.188-2.11h-2.61l.29 3.855L12 19.288l5.373-1.53L18.59 4.414z"/></symbol>
        <symbol id="logo-html5" viewBox="0 0 24 24"><path fill="#E34F26" d="M1.5 0h21l-1.91 21.563L11.977 24l-8.564-2.438L1.5 0zm7.031 9.75l-.232-2.718 10.059.003.23-2.622L5.412 4.41l.698 8.01h9.126l-.326 3.426-2.91.804-2.955-.81-.188-2.11H6.248l.33 4.171L12 19.351l5.379-1.443.744-8.157H8.531z"/></symbol>
        <symbol id="logo-laravel" viewBox="0 0 24 24"><path fill="#FF2D20" d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.011-.021-.022-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/></symbol>
        <symbol id="logo-ts" viewBox="0 0 24 24"><path fill="#3178C6" d="M1.125 0C.502 0 0 .502 0 1.125v21.75C0 23.498.502 24 1.125 24h21.75c.623 0 1.125-.502 1.125-1.125V1.125C24 .502 23.498 0 22.875 0zm17.363 9.75c.612 0 1.154.037 1.627.111a6.38 6.38 0 0 1 1.306.34v2.458a3.95 3.95 0 0 0-.643-.361 5.093 5.093 0 0 0-.717-.26 5.453 5.453 0 0 0-1.426-.2c-.3 0-.573.028-.819.086a2.1 2.1 0 0 0-.623.242c-.17.104-.3.229-.393.374a.888.888 0 0 0-.14.49c0 .196.053.373.156.529.104.156.252.304.443.444s.423.276.696.41c.273.135.582.274.926.416.47.197.892.407 1.266.628.374.222.695.473.963.753.268.279.472.598.614.957.142.359.214.776.214 1.253 0 .657-.125 1.21-.373 1.656a3.033 3.033 0 0 1-1.012 1.085 4.38 4.38 0 0 1-1.487.596c-.566.12-1.163.18-1.79.18a9.916 9.916 0 0 1-1.84-.164 5.544 5.544 0 0 1-1.512-.493v-2.63a5.033 5.033 0 0 0 3.237 1.2c.333 0 .624-.03.872-.09.249-.06.456-.144.623-.25.166-.108.29-.234.373-.38a1.023 1.023 0 0 0-.074-1.089 2.12 2.12 0 0 0-.537-.5 5.597 5.597 0 0 0-.807-.444 27.72 27.72 0 0 0-1.007-.436c-.918-.383-1.602-.852-2.053-1.405-.45-.553-.676-1.222-.676-2.005 0-.614.123-1.141.369-1.582.246-.441.58-.804 1.004-1.089a4.494 4.494 0 0 1 1.47-.629 7.536 7.536 0 0 1 1.77-.201zm-15.113.188h9.563v2.166H9.506v9.646H6.789v-9.646H3.375z"/></symbol>
        <symbol id="logo-node" viewBox="0 0 24 24"><path fill="#5FA04E" d="M11.998 24c-.321 0-.641-.084-.922-.247l-2.936-1.737c-.438-.245-.224-.332-.08-.383.585-.203.703-.25 1.328-.604.065-.037.151-.023.218.017l2.256 1.339c.082.045.197.045.272 0l8.795-5.076c.082-.047.134-.141.134-.238V6.921c0-.099-.053-.192-.137-.242l-8.791-5.072c-.081-.047-.189-.047-.271 0L3.075 6.68C2.99 6.729 2.936 6.825 2.936 6.921v10.15c0 .097.054.189.139.235l2.409 1.392c1.307.654 2.108-.116 2.108-.89V7.787c0-.142.114-.253.256-.253h1.115c.139 0 .255.112.255.253v10.021c0 1.745-.95 2.745-2.604 2.745-.508 0-.909 0-2.026-.551L2.28 18.675c-.57-.329-.922-.945-.922-1.604V6.921c0-.659.353-1.275.922-1.603l8.795-5.082c.557-.315 1.296-.315 1.848 0l8.794 5.082c.57.329.924.944.924 1.603v10.15c0 .659-.354 1.273-.924 1.604l-8.794 5.078C12.643 23.916 12.324 24 11.998 24zM19.099 13.993c0-1.9-1.284-2.406-3.987-2.763-2.731-.361-3.009-.548-3.009-1.187 0-.528.235-1.233 2.258-1.233 1.807 0 2.473.389 2.747 1.607.024.115.129.199.247.199h1.141c.071 0 .138-.031.186-.081.048-.054.074-.123.067-.196-.177-2.098-1.571-3.076-4.388-3.076-2.508 0-4.004 1.058-4.004 2.833 0 1.925 1.488 2.457 3.895 2.695 2.88.282 3.103.703 3.103 1.269 0 .983-.789 1.402-2.642 1.402-2.327 0-2.839-.584-3.011-1.742-.02-.124-.126-.215-.253-.215h-1.137c-.141 0-.254.112-.254.253 0 1.482.806 3.248 4.655 3.248 3.012 0 4.612-1.097 4.612-3.014z"/></symbol>
        <symbol id="logo-python" viewBox="0 0 24 24"><path fill="#3776AB" d="M14.25.18l.9.2.73.26.59.3.45.32.34.34.25.34.16.33.1.3.04.26.02.2-.01.13V8.5l-.05.63-.13.55-.21.46-.26.38-.3.31-.33.25-.35.19-.35.14-.33.1-.3.07-.26.04-.21.02H8.77l-.69.05-.59.14-.5.22-.41.27-.33.32-.27.35-.2.36-.15.37-.1.35-.07.32-.04.27-.02.21v3.06H3.17l-.21-.03-.28-.07-.32-.12-.35-.18-.36-.26-.36-.36-.35-.46-.32-.59-.28-.73-.21-.88-.14-1.05-.05-1.23.06-1.22.16-1.04.24-.87.32-.71.36-.57.4-.44.42-.33.42-.24.4-.16.36-.1.32-.05.24-.01h.16l.06.01h8.16v-.83H6.18l-.01-2.75-.02-.37.05-.34.11-.31.17-.28.25-.26.31-.23.38-.2.44-.18.51-.15.58-.12.64-.1.71-.06.77-.04.84-.02 1.27.05zm-6.3 1.98l-.23.33-.08.41.08.41.23.34.33.22.41.09.41-.09.33-.22.23-.34.08-.41-.08-.41-.23-.33-.33-.22-.41-.09-.41.09z"/><path fill="#FFD43B" d="M21.04 5.11l.28.06.32.12.35.18.36.27.36.35.35.47.32.59.28.73.21.88.14 1.04.05 1.23-.06 1.23-.16 1.04-.24.86-.32.71-.36.57-.4.45-.42.33-.42.24-.4.16-.36.09-.32.05-.24.02-.16-.01h-8.22v.82h5.84l.01 2.76.02.36-.05.34-.11.31-.17.29-.25.25-.31.24-.38.2-.44.17-.51.15-.58.13-.64.09-.71.07-.77.04-.84.01-1.27-.04-1.07-.14-.9-.2-.73-.25-.59-.3-.45-.33-.34-.34-.25-.34-.16-.33-.1-.3-.04-.25-.02-.2.01-.13v-5.34l.05-.64.13-.54.21-.46.26-.38.3-.32.33-.24.35-.2.35-.14.33-.1.3-.06.26-.04.21-.02.13-.01h5.84l.69-.05.59-.14.5-.21.41-.28.33-.32.27-.35.2-.36.15-.36.1-.35.07-.32.04-.28.02-.21V6.07h2.09l.14.01zm-6.47 14.25l-.23.33-.08.41.08.41.23.33.33.23.41.08.41-.08.33-.23.23-.33.08-.41-.08-.41-.23-.33-.33-.23-.41-.08-.41.08z"/></symbol>
        <symbol id="logo-react" viewBox="0 0 24 24"><path fill="#61DAFB" d="M14.23 12.004a2.236 2.236 0 0 1-2.235 2.236 2.236 2.236 0 0 1-2.236-2.236 2.236 2.236 0 0 1 2.235-2.236 2.236 2.236 0 0 1 2.236 2.236zm2.648-10.69c-1.346 0-3.107.96-4.888 2.622-1.78-1.653-3.542-2.602-4.887-2.602-.41 0-.783.093-1.106.278-1.375.793-1.683 3.264-.973 6.365C1.98 8.917 0 10.42 0 12.004c0 1.59 1.99 3.097 5.043 4.03-.704 3.113-.39 5.588.988 6.38.32.187.69.275 1.102.275 1.345 0 3.107-.96 4.888-2.624 1.78 1.654 3.542 2.603 4.887 2.603.41 0 .783-.09 1.106-.275 1.374-.792 1.683-3.263.973-6.365C22.02 15.096 24 13.59 24 12.004c0-1.59-1.99-3.097-5.043-4.032.704-3.11.39-5.587-.988-6.38-.318-.184-.688-.277-1.092-.278zm-.005 1.09v.006c.225 0 .406.044.558.127.666.382.955 1.835.73 3.704-.054.46-.142.945-.25 1.44-.96-.236-2.006-.417-3.107-.534-.66-.905-1.345-1.727-2.035-2.447 1.592-1.48 3.087-2.292 4.105-2.295zm-9.77.02c1.012 0 2.514.808 4.11 2.28-.686.72-1.37 1.537-2.02 2.442-1.107.117-2.154.298-3.113.538-.112-.49-.195-.964-.254-1.42-.23-1.868.054-3.32.714-3.707.19-.09.4-.127.563-.132zm4.882 3.05c.455.468.91.992 1.36 1.564-.44-.02-.89-.034-1.345-.034-.46 0-.915.01-1.36.034.44-.572.895-1.096 1.345-1.565zM12 8.1c.74 0 1.477.034 2.202.093.406.582.802 1.203 1.183 1.86.372.64.71 1.29 1.018 1.946-.308.655-.646 1.31-1.013 1.95-.38.66-.773 1.288-1.18 1.87-.728.063-1.466.098-2.21.098-.74 0-1.477-.035-2.202-.093-.406-.582-.802-1.204-1.183-1.86-.372-.64-.71-1.29-1.018-1.946.303-.657.646-1.313 1.013-1.954.38-.66.773-1.286 1.18-1.868.728-.064 1.466-.098 2.21-.098zm-3.635.254c-.24.377-.48.763-.704 1.16-.225.39-.435.782-.635 1.174-.265-.656-.49-1.31-.676-1.947.64-.15 1.315-.283 2.015-.386zm7.26 0c.695.103 1.365.23 2.006.387-.18.632-.405 1.282-.66 1.933-.2-.39-.41-.783-.64-1.174-.225-.392-.465-.774-.705-1.146zm3.063.675c.484.15.944.317 1.375.498 1.732.74 2.852 1.708 2.852 2.476-.005.768-1.125 1.74-2.857 2.475-.42.18-.88.342-1.355.493-.28-.958-.646-1.956-1.1-2.98.45-1.017.81-2.01 1.085-2.964zm-13.395.004c.278.96.645 1.957 1.1 2.98-.45 1.017-.812 2.01-1.086 2.964-.484-.15-.944-.318-1.37-.5-1.732-.737-2.852-1.706-2.852-2.474 0-.768 1.12-1.742 2.852-2.476.42-.18.88-.342 1.356-.494zm11.678 4.28c.265.657.49 1.312.676 1.948-.64.157-1.316.29-2.016.39.24-.375.48-.762.705-1.158.225-.39.435-.788.636-1.18zm-9.945.02c.2.392.41.783.64 1.175.23.39.465.772.705 1.143-.695-.102-1.365-.23-2.006-.386.18-.63.406-1.282.66-1.933zM17.92 16.32c.112.493.2.968.254 1.423.23 1.868-.054 3.32-.714 3.708-.147.09-.338.128-.563.128-1.012 0-2.514-.807-4.11-2.28.686-.72 1.37-1.536 2.02-2.44 1.107-.118 2.154-.3 3.113-.54zm-11.83.01c.96.234 2.006.415 3.107.532.66.905 1.345 1.727 2.035 2.446-1.595 1.483-3.092 2.295-4.11 2.295-.22-.005-.406-.05-.553-.132-.666-.38-.955-1.834-.73-3.703.054-.46.142-.944.25-1.438zm4.56.64c.44.02.89.034 1.345.034.46 0 .915-.01 1.36-.034-.44.572-.895 1.095-1.345 1.565-.455-.47-.91-.993-1.36-1.565z"/></symbol>
        <symbol id="logo-vue" viewBox="0 0 24 24"><path fill="#4FC08D" d="M24 1.61H14.06L12 5.16 9.94 1.61H0L12 22.39ZM12 14.08 5.16 2.23H9.59L12 6.41l2.41-4.18h4.43Z"/></symbol>
        <symbol id="logo-mysql" viewBox="0 0 24 24"><path fill="#4479A1" d="M16.405 5.501c-.115 0-.193.014-.274.033v.013h.014c.054.104.146.18.214.273.054.107.1.214.154.32l.014-.015c.094-.066.14-.172.14-.333-.04-.047-.046-.094-.08-.14-.04-.067-.126-.1-.18-.153zM5.77 18.695h-.927a50.854 50.854 0 00-.27-4.41h-.008l-1.41 4.41H2.45l-1.4-4.41h-.01a72.892 72.892 0 00-.195 4.41H0c.055-1.966.192-3.81.41-5.53h1.15l1.335 4.064h.008l1.347-4.064h1.095c.242 2.015.384 3.86.428 5.53zm4.017-4.08c-.378 2.045-.876 3.533-1.492 4.46-.482.716-1.01 1.073-1.583 1.073-.153 0-.34-.046-.566-.138v-.494c.11.017.24.026.386.026.268 0 .483-.075.647-.222.197-.18.295-.382.295-.605 0-.155-.077-.47-.23-.944L6.23 14.615h.91l.727 2.36c.164.536.233.91.205 1.123.4-1.064.678-2.227.835-3.483zm12.325 4.08h-2.63v-5.53h.885v4.85h1.745zm-3.32.135l-1.016-.5c.09-.076.177-.158.255-.25.433-.506.648-1.258.648-2.253 0-1.83-.718-2.746-2.155-2.746-.704 0-1.254.232-1.65.697-.43.508-.646 1.256-.646 2.245 0 .972.19 1.686.574 2.14.35.41.877.615 1.583.615.264 0 .506-.033.725-.098l1.325.772.36-.622zM15.5 17.588c-.225-.36-.337-.94-.337-1.736 0-1.393.424-2.09 1.27-2.09.443 0 .77.167.977.5.224.362.336.936.336 1.723 0 1.404-.424 2.108-1.27 2.108-.445 0-.77-.167-.978-.5zm-1.658-.425c0 .47-.172.856-.516 1.156-.344.3-.803.45-1.384.45-.543 0-1.064-.172-1.573-.515l.237-.476c.438.22.833.328 1.19.328.332 0 .593-.073.783-.22a.754.754 0 00.3-.615c0-.33-.23-.61-.648-.845-.388-.213-1.163-.657-1.163-.657-.422-.307-.632-.636-.632-1.177 0-.45.157-.81.47-1.085.315-.278.72-.415 1.22-.415.512 0 .98.136 1.4.41l-.213.476a2.726 2.726 0 00-1.064-.23c-.283 0-.502.068-.654.206a.685.685 0 00-.248.524c0 .328.234.61.666.85.393.215 1.187.67 1.187.67.433.305.648.63.648 1.168zm9.382-5.852c-.535-.014-.95.04-1.297.188-.1.04-.26.04-.274.167.055.053.063.14.11.214.08.134.218.313.346.407.14.11.28.216.427.31.26.16.555.255.81.416.145.094.293.213.44.313.073.05.12.14.214.172v-.02c-.046-.06-.06-.147-.105-.214-.067-.067-.134-.127-.2-.193a3.223 3.223 0 00-.695-.675c-.214-.146-.682-.35-.77-.595l-.013-.014c.146-.013.32-.066.46-.106.227-.06.435-.047.67-.106.106-.027.213-.06.32-.094v-.06c-.12-.12-.21-.283-.334-.395a8.867 8.867 0 00-1.104-.823c-.21-.134-.476-.22-.697-.334-.08-.04-.214-.06-.26-.127-.12-.146-.19-.34-.275-.514a17.69 17.69 0 01-.547-1.163c-.12-.262-.193-.523-.34-.763-.69-1.137-1.437-1.826-2.586-2.5-.247-.14-.543-.2-.856-.274-.167-.008-.334-.02-.5-.027-.11-.047-.216-.174-.31-.235-.38-.24-1.364-.76-1.644-.072-.18.434.267.862.422 1.082.115.153.26.328.34.5.047.116.06.235.107.356.106.294.207.622.347.897.073.14.153.287.247.413.054.073.146.107.167.227-.094.136-.1.334-.154.5-.24.757-.146 1.693.194 2.25.107.166.362.534.703.393.3-.12.234-.5.32-.835.02-.08.007-.133.048-.187v.015c.094.188.188.367.274.555.206.328.566.668.867.895.16.12.287.328.487.402v-.02h-.015c-.043-.058-.1-.086-.154-.133a3.445 3.445 0 01-.35-.4 8.76 8.76 0 01-.747-1.218c-.11-.21-.202-.436-.29-.643-.04-.08-.04-.2-.107-.24-.1.146-.247.273-.32.453-.127.288-.14.642-.188 1.01-.027.007-.014 0-.027.014-.214-.052-.287-.274-.367-.46-.2-.475-.233-1.238-.06-1.785.047-.14.247-.582.167-.716-.042-.127-.174-.2-.247-.303a2.478 2.478 0 01-.24-.427c-.16-.374-.24-.788-.414-1.162-.08-.173-.22-.354-.334-.513-.127-.18-.267-.307-.368-.52-.033-.073-.08-.194-.027-.274.014-.054.042-.075.094-.09.088-.072.335.022.422.062.247.1.455.194.662.334.094.066.195.193.315.226h.14c.214.047.455.014.655.073.355.114.675.28.962.46a5.953 5.953 0 012.085 2.286c.08.154.115.295.188.455.14.33.313.663.455.982.14.315.275.636.476.897.1.14.502.213.682.286.133.06.34.115.46.188.23.14.454.3.67.454.11.076.443.243.463.378z"/></symbol>
        <symbol id="logo-git" viewBox="0 0 24 24"><path fill="#F05032" d="M23.546 10.93L13.067.452c-.604-.603-1.582-.603-2.188 0L8.708 2.627l2.76 2.76c.645-.215 1.379-.07 1.889.441.516.515.658 1.258.438 1.9l2.658 2.66c.645-.223 1.387-.078 1.9.435.721.72.721 1.884 0 2.604-.719.719-1.881.719-2.6 0-.539-.541-.674-1.337-.404-1.996L12.86 8.955v6.525c.176.086.342.203.488.348.713.721.713 1.883 0 2.6-.719.721-1.889.721-2.609 0-.719-.719-.719-1.879 0-2.598.182-.18.387-.316.605-.406V8.835c-.217-.091-.424-.222-.6-.401-.545-.545-.676-1.342-.396-2.009L7.636 3.7.45 10.881c-.6.605-.6 1.584 0 2.189l10.48 10.477c.604.604 1.582.604 2.186 0l10.43-10.43c.605-.603.605-1.582 0-2.187"/></symbol>
    </svg>
</head>
<body class="<?php echo $body_class; ?> auth-body">
    <div class="auth-wrapper auth-wrapper--register">
        <!-- Decorative circles -->
        <div class="auth-deco-circle auth-deco-circle--tl"></div>
        <div class="auth-deco-circle auth-deco-circle--br"></div>

        <!-- Floating Tech Logos Background -->
        <div class="auth-floating-tech">
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-js"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-php"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-css3"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-html5"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-laravel"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-ts"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-node"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-python"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-react"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-vue"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-mysql"/></svg></div>
            <div class="tech-icon"><svg viewBox="0 0 128 128"><use href="#logo-git"/></svg></div>
        </div>

        <!-- LEFT: Welcome Panel (blue gradient) -->
        <div class="auth-welcome-panel">
            <button class="auth-welcome-close" onclick="window.location.href='index.php'" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>

            <div class="auth-welcome-watermark"></div>

            <div class="auth-welcome-content">
                <a href="index.php" class="auth-welcome-brand">
                    <svg class="auth-welcome-brand-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.9"/>
                                <stop offset="100%" stop-color="#bfdbfe" stop-opacity="0.7"/>
                            </linearGradient>
                        </defs>
                        <path d="M 25 20 L 25 75 Q 25 80 30 80 L 35 80 Q 40 80 40 75 L 40 20 Q 40 15 35 15 L 30 15 Q 25 15 25 20 Z" fill="url(#logoGrad)"/>
                        <path d="M 40 20 Q 40 15 45 15 L 60 15 Q 70 15 70 25 L 70 35 Q 70 45 60 45 L 45 45 Q 40 45 40 40 L 40 30 Q 40 25 45 25 L 60 25 Q 65 25 65 30 L 65 35 Q 65 40 60 40 L 45 40 Q 40 40 40 35 Z" fill="url(#logoGrad)"/>
                    </svg>
                    <span class="auth-welcome-brand-name"><?php echo APP_NAME; ?></span>
                </a>

                <h1 class="auth-welcome-heading">GABUNG SEKARANG!</h1>
                <p class="auth-welcome-text">Buat akun dan mulai perjalanan coding Anda. Pelajari pemrograman dengan cara yang menyenangkan dan interaktif.</p>

                <a href="login.php" class="auth-btn-signup">MASUK</a>

                <div class="auth-welcome-stats">
                    <div class="auth-welcome-stat">
                        <strong>10K+</strong>
                        <span>Siswa</span>
                    </div>
                    <div class="auth-welcome-stat-sep"></div>
                    <div class="auth-welcome-stat">
                        <strong>50+</strong>
                        <span>Kursus</span>
                    </div>
                    <div class="auth-welcome-stat-sep"></div>
                    <div class="auth-welcome-stat">
                        <strong>4.9</strong>
                        <span>Rating</span>
                    </div>
                </div>

                <div class="auth-welcome-features">
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">50+ Kursus Interaktif</span>
                    </div>
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">XP, Level & Pencapaian</span>
                    </div>
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">Code Playground Real-time</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Registration Form (white card) -->
        <div class="auth-form-panel auth-form-panel--compact">
            <div class="auth-form-brand">
                <a href="index.php" class="auth-form-brand-link">
                    <svg class="auth-form-brand-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <use href="#brandLogo"></use>
                    </svg>
                    <span class="auth-form-brand-name"><?php echo APP_NAME; ?></span>
                </a>
            </div>
            <div class="auth-form-header">
                <div class="auth-form-title">Buat Akun Baru</div>
                <p class="auth-form-subtitle">Mulai perjalanan coding Anda sekarang</p>
            </div>
            <span class="auth-form-title-underline"></span>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error" role="alert">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm" novalidate>
                <div class="auth-form-row-2 stagger">
                    <div class="auth-field">
                        <label for="nama_lengkap" class="auth-field-label">Nama Lengkap</label>
                        <div class="auth-field-wrap">
                            <span class="auth-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                   class="auth-field-input <?php echo isset($errors['nama_lengkap']) ? 'is-invalid' : ''; ?>"
                                   placeholder="John Doe"
                                   value="<?php echo htmlspecialchars($old['nama_lengkap'] ?? ''); ?>"
                                   required autocomplete="name" minlength="3" maxlength="100">
                        </div>
                        <?php if (isset($errors['nama_lengkap'])): ?>
                            <p class="auth-field-error"><?php echo htmlspecialchars($errors['nama_lengkap']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field">
                        <label for="username" class="auth-field-label">Username</label>
                        <div class="auth-field-wrap">
                            <span class="auth-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
                            </span>
                            <input type="text" id="username" name="username"
                                   class="auth-field-input <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                                   placeholder="johndoe"
                                   value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>"
                                   required autocomplete="username" pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <p class="auth-field-error"><?php echo htmlspecialchars($errors['username']); ?></p>
                        <?php else: ?>
                            <p class="auth-field-help">3-30 karakter, huruf, angka & garis bawah saja</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="auth-field stagger">
                    <label for="email" class="auth-field-label">Alamat Email</label>
                    <div class="auth-field-wrap">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                        </span>
                        <input type="email" id="email" name="email"
                               class="auth-field-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                               placeholder="nama@email.com"
                               value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                               required autocomplete="email">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <p class="auth-field-error"><?php echo htmlspecialchars($errors['email']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="auth-field stagger">
                    <label for="password" class="auth-field-label">Kata Sandi</label>
                    <div class="auth-field-wrap">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password"
                               class="auth-field-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                               placeholder="Minimal 8 karakter"
                               required minlength="8" autocomplete="new-password">
                        <button type="button" class="auth-field-toggle" id="togglePassword" aria-label="Show password">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <div class="auth-strength-bar" aria-hidden="true">
                        <div class="auth-strength-fill <?php echo $password_value ? $strengthClass : ''; ?>" id="strengthBar" style="width: <?php echo $password_value ? $strengthPercent : 0; ?>%"></div>
                    </div>
                    <span class="auth-strength-text" id="strengthLabel"><?php echo $password_value ? $strengthLabel : ''; ?></span>
                    <ul class="auth-req-list" id="requirementList">
                        <li data-req="length" class="<?php echo $hasMinLength ? 'met' : ''; ?>">Minimal 8 karakter</li>
                        <li data-req="case" class="<?php echo $hasUpperLower ? 'met' : ''; ?>">Huruf besar & kecil</li>
                        <li data-req="number" class="<?php echo $hasNumber ? 'met' : ''; ?>">Mengandung angka</li>
                        <li data-req="special" class="<?php echo $hasSpecial ? 'met' : ''; ?>">Karakter khusus</li>
                    </ul>
                    <?php if (isset($errors['password'])): ?>
                        <p class="auth-field-error"><?php echo htmlspecialchars($errors['password']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="auth-field stagger">
                    <label for="password_confirm" class="auth-field-label">Konfirmasi Kata Sandi</label>
                    <div class="auth-field-wrap">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <input type="password" id="password_confirm" name="password_confirm"
                               class="auth-field-input <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                               placeholder="Ulangi kata sandi"
                               required minlength="8" autocomplete="new-password">
                    </div>
                    <p class="auth-field-error" id="matchFeedback" style="display:none">Kata sandi tidak cocok</p>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <p class="auth-field-error"><?php echo htmlspecialchars($errors['password_confirm']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="auth-field stagger">
                    <label class="auth-checkbox-row">
                        <input type="checkbox" name="terms" id="terms" required <?php echo !empty($old['terms']) ? 'checked' : ''; ?>>
                        <span>Saya menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a></span>
                    </label>
                    <?php if (isset($errors['terms'])): ?>
                        <p class="auth-field-error"><?php echo htmlspecialchars($errors['terms']); ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="auth-btn-primary stagger" id="submitBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    <span class="btn-label">DAFTAR</span>
                    <span class="btn-spinner" aria-hidden="true"></span>
                </button>
            </form>

            <div class="auth-divider-sm stagger"><span>atau</span></div>

            <div class="auth-form-footer stagger">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        'use strict';

        var form = document.getElementById('registerForm');
        var password = document.getElementById('password');
        var confirm = document.getElementById('password_confirm');
        var toggle = document.getElementById('togglePassword');
        var strengthBar = document.getElementById('strengthBar');
        var strengthLabel = document.getElementById('strengthLabel');
        var matchFeedback = document.getElementById('matchFeedback');
        var submitBtn = document.getElementById('submitBtn');
        var requirements = {
            length: document.querySelector('[data-req="length"]'),
            case: document.querySelector('[data-req="case"]'),
            number: document.querySelector('[data-req="number"]'),
            special: document.querySelector('[data-req="special"]')
        };

        toggle.addEventListener('click', function() {
            var isPassword = password.type === 'password';
            password.type = isPassword ? 'text' : 'password';
            confirm.type = isPassword ? 'text' : 'password';
            toggle.querySelector('.eye-open').style.display = isPassword ? 'none' : '';
            toggle.querySelector('.eye-closed').style.display = isPassword ? '' : 'none';
            toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });

        function updateStrength() {
            var v = password.value;
            var checks = {
                length: v.length >= 8,
                case: /[A-Z]/.test(v) && /[a-z]/.test(v),
                number: /\d/.test(v),
                special: /[^A-Za-z0-9]/.test(v)
            };
            var score = Object.values(checks).filter(Boolean).length;
            var labels = ['Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            var classes = ['weak', 'weak', 'fair', 'good', 'strong'];
            var percents = [0, 25, 50, 75, 100];

            Object.keys(checks).forEach(function(k) {
                requirements[k].classList.toggle('met', checks[k]);
            });

            if (v.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'auth-strength-fill';
                strengthLabel.textContent = '';
            } else {
                strengthBar.style.width = percents[score] + '%';
                strengthBar.className = 'auth-strength-fill ' + classes[score];
                strengthLabel.textContent = 'Kekuatan: ' + labels[score];
            }
        }

        function updateMatch() {
            if (confirm.value.length === 0) {
                matchFeedback.style.display = 'none';
                confirm.classList.remove('is-invalid');
                return;
            }
            if (password.value !== confirm.value) {
                matchFeedback.style.display = '';
                confirm.classList.add('is-invalid');
            } else {
                matchFeedback.style.display = 'none';
                confirm.classList.remove('is-invalid');
            }
        }

        password.addEventListener('input', function() { updateStrength(); updateMatch(); });
        confirm.addEventListener('input', updateMatch);

        form.addEventListener('submit', function(e) {
            if (password.value !== confirm.value) {
                e.preventDefault();
                matchFeedback.style.display = '';
                confirm.classList.add('is-invalid');
                confirm.focus();
                return;
            }
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
            }
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });

        updateStrength();

        // Focus effect for input groups
        document.querySelectorAll('.auth-field-wrap').forEach(function(wrap) {
            var inp = wrap.querySelector('.auth-field-input');
            if (!inp) return;
            inp.addEventListener('focus', function() { wrap.classList.add('is-focused'); });
            inp.addEventListener('blur', function() { wrap.classList.remove('is-focused'); });
        });

        // Stagger fallback
        var stagers = document.querySelectorAll('.stagger');
        if (stagers.length && !window.requestAnimationFrame) {
            stagers.forEach(function(el) {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
        }
    })();
    </script>
</body>
</html>
