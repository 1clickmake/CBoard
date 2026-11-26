<?php
$isEdit = isset($post);
$title = $isEdit ? '문의 수정' : '문의하기';
include_header($title, $siteConfig);
$old = $old ?? [];
$valAuthor = $old['author_name'] ?? ($isEdit ? ($post['author_name'] ?? '') : ($_SESSION['user']['username'] ?? ''));
$valEmail = $old['email'] ?? ($isEdit ? ($post['email'] ?? '') : ($_SESSION['user']['email'] ?? ''));
$valPhone = $old['phone'] ?? ($isEdit ? ($post['phone'] ?? '') : '');
$valTitle = $old['title'] ?? ($isEdit ? ($post['title'] ?? '') : '');
$valContent = $old['content'] ?? ($isEdit ? ($_raw['post']['content'] ?? $post['content'] ?? '') : '');
$valSecret = isset($old['is_secret']) ? !empty($old['is_secret']) : ($isEdit ? !empty($post['is_secret']) : false);
?>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title"><?= htmlspecialchars($board['title'] ?? 'Contact') ?></h2>

    <div class="form_wrap">
        <div class="leftArea">
            <h4 class="main-tit">세상의 별빛을 공간에 담아내다</h4>
            <p class="main-txt">모든 특별한 공간은 하나의 이야기에서 시작됩니다.</p>
            <p class="main-txt">당신이 꿈꾸는 공간, 그 깊이와 가치를 세별과 함께 완성해 보세요.</p>
            <dl>
                <dt class="stit">문의 전화</dt>
                <dd class="num"><?= htmlspecialchars(!empty($siteConfig['company_tel']) ? $siteConfig['company_tel'] : '010-2082-8798') ?></dd>
            </dl>
            <dl>
                <dt class="stit">E-mail</dt>
                <dd class="num"><?= htmlspecialchars(!empty($siteConfig['company_email']) ? $siteConfig['company_email'] : 'wstsr121@naver.com') ?></dd>
            </dl>
        </div>

        <div class="rightArea">
            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.45);color:#fca5a5;padding:12px 16px;border-radius:8px;font-size:14px;margin-bottom:20px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="sebyeolWriteForm" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="editor_mode" value="html">
                <div class="form_sty">
                    <ul>
                        <li class="tit"><label for="guest-author" class="required">이름</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input id="guest-author" name="author_name" maxlength="80" value="<?= htmlspecialchars($valAuthor) ?>" placeholder="이름을 입력해 주세요" autocomplete="name" required></span>
                        </li>
                        <li class="tit"><label class="">비밀글 설정</label></li>
                        <li class="cont">
                            <input type="checkbox" id="is_secret" name="is_secret" value="1" <?= $valSecret ? 'checked' : '' ?>>
                            <label id="is_secret_label" for="is_secret">비밀글로 문의하기</label>
                        </li>
                    </ul>
                    <ul>
                        <li class="tit"><label for="guest-password" class="<?= $isEdit ? '' : 'required' ?>">비밀번호</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input id="guest-password" type="password" name="password" minlength="4" maxlength="72" autocomplete="new-password" placeholder="<?= $isEdit ? '변경 시에만 입력해 주세요' : '비밀번호를 입력해 주세요' ?>" <?= $isEdit ? '' : 'required' ?>></span>
                        </li>
                        <li class="tit"><label for="guest-password-confirm" class="<?= $isEdit ? '' : 'required' ?>">비밀번호 확인</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input id="guest-password-confirm" type="password" name="password_confirm" minlength="4" maxlength="72" autocomplete="new-password" placeholder="<?= $isEdit ? '변경 시 비밀번호를 다시 입력해 주세요' : '비밀번호를 다시 입력해 주세요' ?>" <?= $isEdit ? '' : 'required' ?>></span>
                        </li>
                    </ul>
                    <ul>
                        <li class="tit"><label for="email" class="required">E-mail</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input type="email" id="email" name="email" value="<?= htmlspecialchars($valEmail) ?>" placeholder="메일주소를 입력해주세요" required></span>
                        </li>
                        <li class="tit"><label for="phone" class="required">연락처</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input type="text" id="phone" name="phone" value="<?= htmlspecialchars($valPhone) ?>" placeholder="연락처를 입력해주세요" required></span>
                        </li>
                    </ul>
                    <ul>
                        <li class="tit"><label for="guest-title" class="required">제목</label></li>
                        <li class="cont">
                            <span class="input_sty round w100"><input id="guest-title" name="title" maxlength="200" value="<?= htmlspecialchars($valTitle) ?>" placeholder="제목을 입력해 주세요" required></span>
                        </li>
                    </ul>
                    <ul>
                        <li class="tit top"><label for="content" class="required">문의내용</label></li>
                        <li class="cont">
                            <textarea id="content" name="content" class="textArea_sty1" placeholder="문의하실 내용을 입력해주세요." required><?= htmlspecialchars($valContent) ?></textarea>
                        </li>
                    </ul>
                    <?php $maxUploadFiles = isset($board['upload_count']) ? max(0, (int)$board['upload_count']) : 5; ?>
                    <?php if ($maxUploadFiles > 0): ?>
                    <ul>
                        <li class="tit top"><label>첨부파일</label></li>
                        <li class="cont gap-2">
                            <div class="inputfile-wrap">
                                <input type="text" id="file-name-display" placeholder="첨부파일 등록 (최대 <?= $maxUploadFiles ?>개)" readonly>
                                <input type="file" id="upload-file1" name="attachments[]" <?= $maxUploadFiles > 1 ? 'multiple' : '' ?> onchange="if (this.files.length > <?= $maxUploadFiles ?>) { alert('최대 <?= $maxUploadFiles ?>개까지 첨부할 수 있습니다.'); this.value=''; document.getElementById('file-name-display').value=''; return; } document.getElementById('file-name-display').value = this.files.length > 1 ? (this.files[0].name + ' 외 ' + (this.files.length - 1) + '개') : (this.files[0] ? this.files[0].name : '');">
                                <label for="upload-file1" class="btn"><span>찾아보기</span></label>
                            </div>
                        </li>
                    </ul>
                    <?php endif; ?>
                    <?php if (!$isEdit): ?>
                    <ul>
                        <li class="tit top"><label class="required">스팸방지입력</label></li>
                        <li class="cont gap-2">
                            <p class="captcha-text">
                                Security Code: <span><?= htmlspecialchars($captcha_q ?? '') ?></span>
                            </p>
                            <span class="input_sty round captcha-input"><input type="text" name="captcha_answer" placeholder="정답을 입력해주세요" required inputmode="numeric" autocomplete="off"></span>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div><!-- // form_sty -->

                <?php if (!$isEdit): ?>
                <h3 class="tit-agreement">개인정보 수집 및 이용동의(필수)</h3>
                <div class="wr_box" data-lenis-prevent-wheel>
                    <h3>개인정보의 수집 및 이용에 대한 안내</h3>
                    <p>귀하의 개인정보보호를 매우 중요시하며, 개인정보보호방침을 통하여 귀하께서 제공하시는 개인정보가 어떠한 용도와 방식으로 이용되고 있으며 개인정보보호를 위해 어떠한 조치가 취해지고 있는지 알려드립니다.</p>
                    <p>
                        ■ 수집항목<br>
                        - 작성자 이름 : 상담 신청 등의 서비스 이용에 따른 본인 확인 절차에 이용<br>
                        - 이메일주소, 전화번호 : 상담 신청 처리 등을 위한 원활한 의사소통 경로의 확보
                    </p>
                    <p>
                        ■ 이용목적<br>
                        의뢰 하고자 하는 이용자의 확인 자료로써 귀하의 원하고자 하는 최적 서비스를 제공하기 위한 목적으로 귀하의 개인정보를 수집ㆍ이용하고 있습니다.<br>
                        문의 및 상담에 있어 필요한 정보를 수집합니다.
                    </p>
                    <p>
                        ■ 개인정보의 보유 및 이용기간<br>
                        - 이용자의 개인정보는 원칙적으로 개인정보의 수집 및 이용목적이 달성되면 지체 없이 파기 합니다.<br>
                        - 다만, 회사 내부 방침에 의한 정보보유 사유로 고객사의 원활한 서비스 제공을 위해 회사명, 고객명, 이메일주소, 전화번호, 기타 선택 항목을 1년간 보유 할 수 있습니다.<br>
                        - 상법, 전자상거래 등에서의 소비자보호에 관한 법률 등 관계법령의 규정에 의하여 보존할 필요가 있는 경우에는 일정기간 보존합니다.<br>
                        * 소비자의 불만 또는 분쟁처리에 관한 기록 : 3년 / 전자상거래등에서의 소비자보호에 관한 법률(본인확인에 관한 기록 6개월, 방문에 관한 기록 3개월)
                    </p>
                </div>
                <div class="right-chkBox">
                    <input name="agree" type="checkbox" id="agreeyes" value="yes" required />
                    <label for="agreeyes">개인정보 제공에 동의합니다.</label>
                </div>
                <?php endif; ?>

                <div class="board-btn-group">
                    <button class="btn-write" type="submit" style="border:none;cursor:pointer;"><?= $isEdit ? '수정' : '등록' ?></button>
                    <a class="btn-cancel" href="/board/<?= htmlspecialchars($board['slug']) ?>">취소</a>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.getElementById('sebyeolWriteForm')?.addEventListener('submit', function(e) {
    var pw = document.getElementById('guest-password')?.value || '';
    var pwc = document.getElementById('guest-password-confirm')?.value || '';
    if (pw !== pwc) {
        e.preventDefault();
        alert('비밀번호와 비밀번호 확인이 일치하지 않습니다.');
        document.getElementById('guest-password-confirm')?.focus();
    }
});
</script>
<?php include_footer($siteConfig); ?>
