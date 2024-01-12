<?php
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var array $data
 * @var CView $this
 */

require_once dirname(__FILE__).'/../page_header.php';
$error = null;

if (array_key_exists('error', $data) && $data['error']) {
	// remove debug code for login form message, trimming not in regex to relay only on [ ] in debug message.
	$message = trim(preg_replace('/\[.*\]/', '', $data['error']['message']));
	$error = (new CDiv($message))->addClass(ZBX_STYLE_RED);
}

global $ZBX_SERVER_NAME;

if ($data['mfa']['type'] == MFA_TYPE_DUO) {
	$form = (new CForm())
		->addVar('request', '1')
		->addItem([$error]);
}

if ($data['mfa']['type'] == MFA_TYPE_TOTP) {
	if (array_key_exists('qr_code', $data)) {
		switch ($data['mfa']['hash_function']) {
			case TOTP_HASH_SHA256:
				$hash_function = 'SHA256';
				break;

			case TOTP_HASH_SHA512:
				$hash_function = 'SHA512';
				break;

			default:
				$hash_function = 'SHA1';
		}

		$form = (new CForm())
			->addItem(hasRequest('request') ? new CVar('request', getRequest('request')) : null)
			->addVar('totp_secret', $data['totp_secret'])
			->addVar('qr_code_url', $data['qr_code_url'])
			->addItem([
				(new CDiv(_('Scan this QR code')))->setAttribute('style', 'text-align: center; font-size: 20px'),
				new CDiv(_('Please scan and get your verification code displayed in your authenticator app.')),
				(new CImg($data['qr_code']))->setAttribute('style', ' margin-left: auto; margin-right: auto;'),
				new CDiv(
					_s('Unable to scan? You can use %1$s secret key to manually configure your authenticator app:',
						$hash_function)),
				new CDiv($data['totp_secret'])
			])
			->addItem(
				(new CList())
					->addItem([
						new CLabel(_('Verification code'), 'verification_code'),
						(new CTextBox('verification_code'))->setAttribute('autofocus', 'autofocus'),
						$error
					])
					->addItem(new CSubmit('enter', _('Sign in')))
			);
	}
	else {
		$form = (new CForm())
			->addItem(hasRequest('request') ? new CVar('request', getRequest('request')) : null)
			->addItem(
				(new CList())
					->addItem([
						new CLabel(_('Verification code'), 'verification_code'),
						(new CTextBox('verification_code'))->setAttribute('autofocus', 'autofocus'),
						$error
					])
					->addItem(new CSubmit('enter', _('Sign in')))
			);
	}
}

(new CDiv([
	(new CTag('main', true, [
		(isset($ZBX_SERVER_NAME) && $ZBX_SERVER_NAME !== '')
			? (new CDiv($ZBX_SERVER_NAME))->addClass(ZBX_STYLE_SERVER_NAME)
			: null,
		(new CDiv([
			(new CDiv(makeLogo(LOGO_TYPE_NORMAL)))->addClass(ZBX_STYLE_SIGNIN_LOGO),
			$form
		]))->addClass(ZBX_STYLE_SIGNIN_CONTAINER),
		(new CDiv([
			(new CLink(_('Help'), CBrandHelper::getHelpUrl()))
				->setTarget('_blank')
				->addClass(ZBX_STYLE_GREY)
				->addClass(ZBX_STYLE_LINK_ALT),
			CBrandHelper::isRebranded() ? null : [NBSP(), NBSP(), BULLET(), NBSP(), NBSP()],
			CBrandHelper::isRebranded()
				? null
				: (new CLink(_('Support'), getSupportUrl(CWebUser::getLang())))
				->setTarget('_blank')
				->addClass(ZBX_STYLE_GREY)
				->addClass(ZBX_STYLE_LINK_ALT)
		]))->addClass(ZBX_STYLE_SIGNIN_LINKS)
	])),
	makePageFooter(false)
]))
	->addClass(ZBX_STYLE_LAYOUT_WRAPPER)
	->show();
?>
</body>
