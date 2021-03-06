<?php

function response_error($msg) {
        http_response_code(400);

        $response = (object) [
                'msg' => $msg
        ];

        echo json_encode($response);
        exit(1);
}

include('conf.php');

$langs = [
	'en' => [
		'intro' => 'Do you have some feedback? Write and send it from here!',
		'title' => 'Brief title',
		'text' => 'Complete text for your message. Please be as specific as possible!',
		'send' => 'Send!',
		'wait' => 'Wait...',
		'oops' => 'Oops... An error occourred...',
		'error_title' => "Parameter 'title' has not been defined",
		'error_content' => "Parameter 'contents' has not been defined",
		'error_save' => 'Error while saving your feedback',
		'final' => '<p>Your note has been saved <a target="_blank" href="URL">here</a></p><p>Thanks for your contribution!</p>'
	],
	
	'it' => [
		'intro' => 'Vuoi inviare una segnalazione? Scrivila e mandala da qui!',
		'title' => 'Breve titolo',
		'text' => 'Testo del tuo messaggio. Sii quanto più preciso possibile!',
		'send' => 'Invia!',
		'wait' => 'Attendi...',
		'oops' => "Oops... C'è stato un errore...",
		'error_title' => "Il parametro 'titolo' non è stato definito",
		'error_content' => "Il parametro 'contenuto' non è stato definito",
		'error_save' => 'Errore nel salvataggio della tua segnalazione',
		'final' => '<p>La tua nota è stata salvata <a target="_blank" href="URL">qui</a></p><p>Grazie per il contributo!</p>'
	]
];

$lang = 'en';
if (isset($_REQUEST['lang']) && isset($langs[$_REQUEST['lang']]))
	$lang = $_REQUEST['lang'];

switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
                if (!isset($_POST['title']) || empty($_POST['title']))
                        response_error($langs[$lang]['error_title']);

                if (!isset($_POST['contents']) || empty($_POST['contents']))
                        response_error($langs[$lang]['error_content']);

                if (isset($conf['fixed_project']) && !empty($conf['fixed_project']))
                        $project = $conf['fixed_project'];
                else if (isset($_POST['project']) && !empty($_POST['project']))
                        $project = $_POST['project'];
                else
                        $project = $conf['default_project'];

                $url = sprintf('https://api.github.com/repos/%s/issues', $project);
                $issue = (object) [
                        'title' => $_POST['title'],
                        'body' => $_POST['contents']
                ];

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $conf['username'], $conf['password']));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERAGENT, 'VoiceHub');

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($issue));

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.github.v3.json+json', 'Content-Type: application/json'));

                $response = curl_exec($ch);

                $response = json_decode($response);
                if (isset($response->html_url)) {
                        $r = (object) [
                                'url' => $response->html_url
                        ];
                        echo json_encode($r);
                }
                else {
                        response_error($langs[$lang]['error_save']);
                }

                break;

        case 'GET':
                ?>

                +function ($) {
                        var VoiceHub = function() {
                                this.project = '<?php

                                if (isset($conf['fixed_project']) && !empty($conf['fixed_project']))
                                        echo $conf['fixed_project'];
                                else if (isset($_GET['project']) && !empty($_GET['project']))
                                        echo $_GET['project'];
                                else
                                        echo $conf['default_project'];

                                ?>';

                                this.container = $('<div>')
                                        .attr('class', 'voicehub')
                                        .css('padding', '10px')
                                        .css('position', 'absolute')
                                        .css('top', '40%')
                                        .css('width', '300px')
                                        .css('right', '-300px')
                                        .css('background-color', '#FFF')
                                        .css('border-top-left-radius', '10px')
                                        .css('border-bottom-left-radius', '10px')
                                        .appendTo('body').append('<p class="intro"><?php echo $langs[$lang]['intro'] ?></p>');

                                this.tab = $('<div>')
                                        .css('position', 'absolute')
                                        .css('top', '40%')
                                        .css('right', this.container.outerWidth() - 30)
                                        .css('-webkit-transform', 'rotate(270deg)')
                                        .css('-moz-transform', 'rotate(270deg)')
                                        .css('-o-transform', 'rotate(270deg)')
                                        .css('writing-mode', 'lr-tb')
                                        .css('padding', '10px')
                                        .css('background-color', '#99E432')
                                        .text('FEEDBACK')
                                        .appendTo(this.container)
                                        .click(
                                                function() {
                                                        if (window.voicehub.container.hasClass('open'))
                                                                window.voicehub.container.animate({'right': '-300px'}, 300).removeClass('open');
                                                        else
                                                                window.voicehub.container.animate({'right': '0'}, 300).addClass('open');
                                                }
                                        );

                                this.form = $('<form>')
                                        .attr('method', 'POST')
                                        .attr('action', '<?php echo 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] ?>')
                                        .appendTo(this.container)
                                        .submit(
                                                function(e) {
                                                        e.preventDefault();
                                                        $(this).find('button').text('<?php echo $langs[$lang]['wait'] ?>').attr('disabled', 'disabled');

                                                        $.ajax($(this).attr('action'), {
                                                                method: $(this).attr('method'),
                                                                data: {
                                                                        project: window.voicehub.project,
                                                                        title: $(this).find('[name=title]').val(),
                                                                        contents: $(this).find('[name=contents]').val()
                                                                },
                                                                dataType: 'json',
                                                                success: function(response) {
                                                                        window.voicehub.container.empty().append('<?php echo $langs[$lang]['final'] ?>'.replace('URL', response.url));
                                                                },
                                                                error: function(response) {
                                                                        window.voicehub.container.find('.intro').text("<?php echo $langs[$lang]['oops'] ?>");
                                                                        window.voicehub.container.find('form button').text('<?php echo $langs[$lang]['send'] ?>').removeAttr('disabled');
                                                                }
                                                        });

                                                        return false;
                                                }
                                        );

                                this.title = $('<input>')
                                        .attr('type', 'text')
                                        .attr('name', 'title')
                                        .attr('placeholder', "<?php echo $langs[$lang]['title'] ?>")
                                        .css('padding', '10px')
                                        .css('width', '100%')
                                        .css('display', 'block')
                                        .appendTo(this.form);

                                this.content = $('<textarea>')
                                        .attr('name', 'contents')
                                        .attr('placeholder', "<?php echo $langs[$lang]['text'] ?>")
                                        .attr('rows', '5')
                                        .css('padding', '10px')
                                        .css('width', '100%')
                                        .css('display', 'block')
                                        .appendTo(this.form);

                                this.submit = $('<button>')
                                        .attr('type', 'submit')
                                        .css('padding', '10px')
                                        .css('width', '100%')
                                        .css('display', 'block')
                                        .text('<?php echo $langs[$lang]['send'] ?>')
                                        .appendTo(this.form);

                                return this;
                        }

                        VoiceHub.prototype.attach = function(text) {
                                /*
                                        TODO
                                */
                        }

                        VoiceHub.prototype.msg = function(message) {
                                /*
                                        TODO
                                */
                        }

                        VoiceHub.prototype.reference = function(user) {
                                /*
                                        TODO
                                */
                        }

                        $.fn.voicehub = VoiceHub
                }(jQuery);

                $(document).ready(function(){
                        $('html').css('overflow-x', 'hidden');
                        window.voicehub = $('body').voicehub();
                });

                <?php

                break;
}

