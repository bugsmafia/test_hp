/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    HYPERPC
 * @license    Proprietary
 * @copyright  Proprietary https://hyperpc.ru/license
 * @link       https://github.com/HYPER-PC/HYPERPC".
 *
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurationActionsNote', {}, {

        $toggler : undefined,
        $textWrapper : undefined,
        $form: undefined,
        textareaHasChanges : false,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$toggler = $this.$('.jsElementNoteToggler');
            $this.$textWrapper = $this.$('.jsElementNoteTextWrapper');
            $this.$form = $this.$('form');

            $this.$('textarea').on('input propertychanged', function() {
                $this._handleTextareaChanged($this);
            });
        },

        /**
         * Handle textarea changed
         *
         * @param $this
         */
        _handleTextareaChanged : function ($this) {
            if (!$this.textareaHasChanges) {
                $this.$('.jsSaveNote').removeAttr('disabled');
            }
            $this.textareaHasChanges = true;
        },

        /**
         * Hide form
         *
         * @param $this
         */
        _hideForm : function ($this) {
            const $textarea = $this.$form.find('textarea'),
                  text = $this.$textWrapper.find('.jsElementNoteText').text();

            $textarea.val(text);
            if (text.trim() !== '') {
                $this.$textWrapper.removeAttr('hidden');
            } else {
                $this.$toggler.removeAttr('hidden');
            }

            $this.textareaHasChanges = false;
            $this.$form.attr('hidden', 'hidden')
                .find('.jsSaveNote').attr('disabled', 'disabled');
        },

        /**
         * Show form
         *
         * @param $this
         */
        _showForm : function ($this) {
            $this.$form.removeAttr('hidden');
            $this.$toggler.attr('hidden', 'hidden');
            $this.$textWrapper.attr('hidden', 'hidden');

            const $textarea = $this.$form.find('textarea'),
                  text = $textarea.val();
            $textarea.focus().val('').val(text);
        },

        /**
         * Show leave a note button
         *
         * @param $this
         */
        _showToggler : function ($this) {
            $this.$toggler.removeAttr('hidden');
            $this.$form.attr('hidden', 'hidden').find('textarea').val('');
            $this.$textWrapper.attr('hidden', 'hidden').find('.jsElementNoteText').text('');
        },

        /**
         * Lock load more button.
         *
         * @param $button - jQuery object
         */
        _lockSubmitButton : function ($button) {
            $button
                .attr('disabled', 'disabled')
                .before('<span uk-spinner="ratio: 0.7"></span> ');
        },

        /**
         * Unlock load more button.
         *
         * @param $this
         * @param $button - jQuery object
         */
        _unlockSubmitButton : function ($this, $button) {
            if ($this.textareaHasChanges) {
                $button.removeAttr('disabled');
            }
            $button.prev('[uk-spinner]').remove();
        },

        /**
         * Handle save note
         *
         * @param $this
         *
         * @returns {Promise}
         */
        _handleSaveNote : function ($this) {
            const $textarea = $this.$form.find('textarea');
            const task = $textarea.val().trim() === '' ? 'ajax-remove' : 'ajax-save';
            const ajax = $.ajax({
                'type'     : 'POST',
                'dataType' : 'json',
                'url'      : '/index.php',
                'data'     : {
                    'format'    : null,
                    'tmpl'      : 'component',
                    'option'    : 'com_hyperpc',
                    'task'      : 'note.' + task,
                    'jform'     : {
                        'id'          : $textarea.data('id'),
                        'context'     : $textarea.data('context'),
                        'item_id'     : $textarea.data('item_id'),
                        'note'        : $textarea.val()
                    }
                },
                'headers' : {
                    'X-CSRF-Token' : $this.getOption('token')
                }
            })
            .done(function(response) {
                if (response.result) {
                    $textarea.data('id', (response.id) ? response.id : 0);
                    $this.$('.jsElementNoteText').text(response.note || '');
                    $this.textareaHasChanges = false;
                    $this._hideForm($this);
                } else {
                    UIkit.notification(response.message, 'danger');
                }
            })
            .fail(function() {
                UIkit.notification('Connection error', 'danger');
            });

            return ajax;
        },

        /**
         * Handle remove note error
         *
         * @param $this
         * @param {string} prevText
         */
        _handleRemoveNoteError : function ($this, prevText) {
            $this.$toggler.attr('hidden', 'hidden');
            $this.$form.attr('hidden', 'hidden').find('textarea').val(prevText);
            $this.$textWrapper.removeAttr('hidden', 'hidden').find('.jsElementNoteText').text(prevText);
        },

        /**
         * On click note toggler
         *
         * @param e
         * @param $this
         */
        'click .jsElementNoteToggler' : function (e, $this) {
            e.preventDefault();
            $this._showForm($this);
        },

        /**
         * On click edit button
         *
         * @param e
         * @param $this
         */
        'click .jsEditNote' : function (e, $this) {
            $this._showForm($this);
        },

        /**
         * On click cancel editing
         *
         * @param e
         * @param $this
         */
        'click .jsElementNoteCancelForm' : function (e, $this) {
            e.preventDefault();
            $this._hideForm($this);
        },

        /**
         * Remove note.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveNote' : function (e, $this) {
            const prevText = $this.$textWrapper.find('.jsElementNoteText').text();
            $this.$form.find('textarea').val('');
            $this._showToggler($this);
            $this._handleSaveNote($this)
                .done(function(response) {
                    if (response.result) {
                        $this.textareaHasChanges = false;
                        $this.$form.find('.jsSaveNote').attr('disabled', 'disabled');
                    } else {
                        $this._handleRemoveNoteError($this, prevText);
                    }
                })
                .fail(function() {
                    $this._handleRemoveNoteError($this, prevText);
                });
        },

        /**
         * Save note.
         *
         * @param e
         * @param $this
         */
        'click .jsSaveNote' : function (e, $this) {
            e.preventDefault();
            const $button = $(this);

            $this._lockSubmitButton($button);
            $this._handleSaveNote($this)
                .always(function() {
                    $this._unlockSubmitButton($this, $button);
                });
        },

        /**
         * On keydown inside note textarea
         *
         * @param e
         * @param $this
         */
        'keydown textarea' : function (e, $this) {
            switch (e.which) {
                case 13: // Enter
                    e.preventDefault();
                    if ($this.textareaHasChanges) {
                        const $button = $this.$form.find('.jsSaveNote');

                        $this._lockSubmitButton($button);
                        $this._handleSaveNote($this)
                            .always(function() {
                                $this._unlockSubmitButton($this, $button);
                            });
                    } else {
                        $this._hideForm($this);
                    }
                    break;
                case 27: // Esc
                    e.preventDefault();
                    $this._hideForm($this);
                    break;
            }
        }
    });
});
