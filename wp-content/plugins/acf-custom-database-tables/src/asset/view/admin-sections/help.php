<?php
$data                   = isset( $data ) ? $data : new stdClass();
$documentation_sections = isset( $data->documentation_sections ) ? $data->documentation_sections : [];
$support_email          = isset( $data->support_email ) ? $data->support_email : '';
$support_mailto         = isset( $data->support_mailto ) ? $data->support_mailto : '';
$system_checks          = isset( $data->system_checks ) ? $data->system_checks : [];
$system_check_data      = isset( $data->system_check_data ) ? $data->system_check_data : [];
?>
<div class="acfcdt-2col">

    <div class="acf-box" id="acfcdt-getting-started">
        <div class="title">
            <h3>Documentation</h3>
        </div>
        <div class="inner">

			<?php if ( $documentation_sections ): ?>
				<?php foreach ( $documentation_sections as $section ): ?>
                    <div class="acfcdt-doc-section">

						<?php if ( isset( $section['title'] ) and $section['title'] ): ?>
                            <h3 class="acfcdt-doc-section-title"><?php echo $section['title'] ?></h3>
						<?php endif; ?>

						<?php if ( isset( $section['content'] ) and $section['content'] ): ?>
							<?php echo $section['content'] ?>
						<?php endif; ?>

						<?php if ( isset( $section['blocks'] ) and $section['blocks'] ): ?>
							<?php foreach ( $section['blocks'] as $block ): ?>
                                <div class="acfcdt-doc-block">

									<?php if ( isset( $block['title'] ) and $block['title'] ): ?>
                                        <h4 class="acfcdt-doc-block-title"><?php echo $block['title'] ?></h4>
									<?php endif; ?>

									<?php if ( isset( $block['content'] ) and $block['content'] ): ?>
                                        <div class="acfcdt-doc-block-content">
											<?php echo $block['content'] ?>
                                        </div>
									<?php endif; ?>

                                </div>
							<?php endforeach; ?>
						<?php endif; ?>

                    </div>
				<?php endforeach; ?>
			<?php else: ?>
                <p>There was a problem loading up the remote documentation for this plugin. There could be a
                    problem with your internet connection or with the remote server, so give it another try
                    shortly. If the problem persists, please let us know.</p>
			<?php endif; ?>

        </div>
    </div>

    <div class="acf-box" id="acfcdt-support">
        <div class="title">
            <h3>Support</h3>
        </div>
        <div class="inner">
            <p>Need help? Copy the diagnostic information below and paste it into an email along with a
                description of the problem. It would also be helpful if you could package up all your table
                definition JSON files and attach that to the support request.</p>
            <p>
                <a class="button button-primary button-large"
                   href="<?php echo $support_mailto ?>">
                    Send us an email at <em><?php echo $support_email ?></em></a>
            </p>

            <h4>Diagnostic Information</h4>

            <p>This information will help us to understand more about your system. Send this along with any
                support request emails to ensure we can help you resolve any issues as fast as possible.</p>
            <div class="acfcdt-diagnostic-data-wrap">
                        <textarea id="acfcdt-diagnostic-data"
                                  class="acfcdt-diagnostic-data"
                                  autocomplete="off"
                                  readonly=""
                                  onclick="this.focus();this.select()"
                                  wrap="off"><?php
	                        echo 'SYSTEM CHECKS:' . PHP_EOL;
	                        foreach ( $system_checks as $check ) {
		                        echo "\t" . $check['name'] . ": " . ( $check['test'] ? 'Pass' : 'Fail' ) . PHP_EOL;
	                        }
	                        echo PHP_EOL;
	                        foreach ( $system_check_data as $datum ) {
		                        echo $datum['name'] . ": " . PHP_EOL;
		                        echo "\t" . $datum['value'] . PHP_EOL . PHP_EOL;
	                        }
	                        ?></textarea>
            </div>
            <div id="acfcdt-copy-success" class="acfcdt-copy-success">
                <div class="acfcdt-copy-success__inner">
                    Diagnostic data has been copied to your clipboard.
                </div>
            </div>
            <button class="button button-primary button-large" id="acfcdt-toggle-diagnostics">Show Diagnostic
                Data
            </button>
            <button class="button button-primary button-large" id="acfcdt-diagnostic-copy">Copy Data to
                Clipboard
            </button>
        </div>
    </div>

</div>