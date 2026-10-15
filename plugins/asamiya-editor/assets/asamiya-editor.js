/**
 * Asamiya Editor (Vanilla JS)
 * Zero-dependency, ultra-lightweight pure Vanilla JS web editor
 * 의존성 0% 순수 바닐라 자바스크립트 초경량 웹 에디터
 */

class AsamiyaEditor {
    constructor(selector, options = {}) {
        this.container = document.querySelector(selector);
        if (!this.container) {
            console.error(`AsamiyaEditor Error: Selector '${selector}' not found.`);
            return;
        }

        this.options = {
            placeholder: options.placeholder || '여기에 내용을 입력하세요...',
            height: options.height || '300px',
        };

        this.isHTMLMode = false; // Track current mode state / 현재 모드 상태 추적

        // Store initial content / 기존 내용을 보관
        this.initialContent = this.container.innerHTML;
        this.container.innerHTML = ''; // Clear container / 컨테이너 비우기

        this.initUI();
        this.bindEvents();
    }

    initUI() {
        // Create wrapper / 래퍼 생성
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'asamiya-editor-wrapper';

        // Create toolbar / 툴바 생성
        this.toolbar = document.createElement('div');
        this.toolbar.className = 'asamiya-toolbar';
        this.toolbar.innerHTML = this.getToolbarHTML();

        // Create editable content area / 편집 영역 생성
        this.contentArea = document.createElement('div');
        this.contentArea.className = 'asamiya-content';
        this.contentArea.contentEditable = true;
        this.contentArea.style.height = this.options.height;
        this.contentArea.setAttribute('data-placeholder', this.options.placeholder);
        this.contentArea.innerHTML = this.initialContent;

        // Create textarea for HTML editing / HTML 편집용 textarea 생성
        this.htmlArea = document.createElement('textarea');
        this.htmlArea.className = 'asamiya-html-area';
        this.htmlArea.style.height = this.options.height;
        this.htmlArea.spellcheck = false;

        // Assemble components / 조립
        this.wrapper.appendChild(this.toolbar);
        this.wrapper.appendChild(this.contentArea);
        this.wrapper.appendChild(this.htmlArea);
        this.container.appendChild(this.wrapper);

        // Enable CSS styling mode by default (e.g., uses <span style="font-weight:bold"> instead of <b>)
        document.execCommand('defaultParagraphSeparator', false, 'p');
        document.execCommand('styleWithCSS', false, true);
    }

    getToolbarHTML() {
        // Icons use FontAwesome classes (Can be replaced with SVG if needed)
        return `
            <div class="asamiya-toolbar-group">
                <select class="asamiya-select" id="asamiya-fontsize" title="글자 크기">
                    <option value="">크기</option>
                    <option value="12px">12px</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                    <option value="20px">20px</option>
                    <option value="24px">24px</option>
                    <option value="30px">30px</option>
                    <option value="36px">36px</option>
                </select>
            </div>
            <div class="asamiya-toolbar-group">
                <input type="color" class="asamiya-color-picker" id="asamiya-backcolor" title="배경 색상" value="#ffff00">
                <input type="color" class="asamiya-color-picker" id="asamiya-forecolor" title="글자 색상" value="#4facfe">
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="undo" title="실행 취소"><i class="fas fa-undo"></i></button>
                <button class="asamiya-btn" data-command="redo" title="다시 실행"><i class="fas fa-redo"></i></button>
                <button class="asamiya-btn" data-command="removeFormat" title="서식 지우기"><i class="fas fa-eraser"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="bold" title="굵게"><i class="fas fa-bold"></i></button>
                <button class="asamiya-btn" data-command="italic" title="기울임"><i class="fas fa-italic"></i></button>
                <button class="asamiya-btn" data-command="underline" title="밑줄"><i class="fas fa-underline"></i></button>
                <button class="asamiya-btn" data-command="strikeThrough" title="취소선"><i class="fas fa-strikethrough"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="formatBlock" data-value="H1" title="제목 1"><b>H1</b></button>
                <button class="asamiya-btn" data-command="formatBlock" data-value="H2" title="제목 2"><b>H2</b></button>
                <button class="asamiya-btn" data-command="formatBlock" data-value="P" title="본문"><b>P</b></button>
                <button class="asamiya-btn" data-command="formatBlock" data-value="BLOCKQUOTE" title="인용구"><i class="fas fa-quote-right"></i></button>
                <button class="asamiya-btn" data-command="insertHorizontalRule" title="구분선 삽입"><i class="fas fa-minus"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="justifyLeft" title="왼쪽 정렬"><i class="fas fa-align-left"></i></button>
                <button class="asamiya-btn" data-command="justifyCenter" title="가운데 정렬"><i class="fas fa-align-center"></i></button>
                <button class="asamiya-btn" data-command="justifyRight" title="오른쪽 정렬"><i class="fas fa-align-right"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="insertUnorderedList" title="기호 목록"><i class="fas fa-list-ul"></i></button>
                <button class="asamiya-btn" data-command="insertOrderedList" title="번호 목록"><i class="fas fa-list-ol"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="createLink" title="링크 삽입"><i class="fas fa-link"></i></button>
                <button class="asamiya-btn" data-command="insertImageBase64" title="이미지 삽입"><i class="fas fa-image"></i></button>
                <button class="asamiya-btn" data-command="insertYoutube" title="유튜브 삽입"><i class="fab fa-youtube"></i></button>
                <button class="asamiya-btn" data-command="insertTable" title="표 삽입"><i class="fas fa-table"></i></button>
            </div>
            <div class="asamiya-toolbar-group">
                <button class="asamiya-btn" data-command="toggleHTML" title="HTML 편집"><i class="fas fa-code"></i></button>
            </div>
        `;
    }

    bindEvents() {
        const buttons = this.toolbar.querySelectorAll('.asamiya-btn');
        
        buttons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent focus loss / 포커스 잃음 방지
                
                const command = btn.getAttribute('data-command');
                let value = btn.getAttribute('data-value') || null;

                if (command === 'createLink') {
                    value = prompt('링크 URL을 입력하세요:', 'http://');
                    if (!value) return;
                    
                    this.contentArea.focus();
                    const selection = window.getSelection();
                    // 드래그한 텍스트가 있으면 그 텍스트를 유지하고, 없으면 URL 자체를 텍스트로 사용
                    const selectedText = selection.toString() || value;
                    const linkHTML = `<a href="${value}" target="_blank">${selectedText}</a>`;
                    document.execCommand('insertHTML', false, linkHTML);
                    
                    this.updateToolbarState(buttons);
                    return;
                }

                if (command === 'insertImageBase64') {
                    this.handleImageUpload();
                    return;
                }

                if (command === 'insertYoutube') {
                    this.handleYoutubeEmbed();
                    return;
                }

                if (command === 'insertTable') {
                    this.handleTableInsert();
                    return;
                }

                if (command === 'toggleHTML') {
                    this.toggleHTMLMode(btn);
                    return;
                }

                // Ignore other formatting buttons when in HTML mode
                if (this.isHTMLMode) return;

                // Execute command / 명령어 실행
                document.execCommand(command, false, value);
                
                // Return focus to content area and update button states immediately
                this.contentArea.focus();
                this.updateToolbarState(buttons);
            });
        });

        // Font size select box event / 폰트 사이즈 셀렉트 박스 이벤트
        const fontSizeSelect = this.toolbar.querySelector('#asamiya-fontsize');
        if (fontSizeSelect) {
            fontSizeSelect.addEventListener('change', (e) => {
                if (this.isHTMLMode) return;
                const size = e.target.value;
                if (!size) return;

                this.contentArea.focus();
                
                // Temporarily disable CSS style mode to force legacy <font> tag generation for Chrome/Edge compatibility
                document.execCommand('styleWithCSS', false, false);
                document.execCommand('fontSize', false, '7');
                document.execCommand('styleWithCSS', false, true); // Restore CSS style mode
                
                // Find the generated <font size="7"> tags and convert them to pixel-based CSS
                const fontElements = this.contentArea.querySelectorAll('font[size="7"]');
                fontElements.forEach(el => {
                    el.removeAttribute('size');
                    el.style.fontSize = size;
                });
                
                // Reset selection (return to 'Size' placeholder)
                e.target.value = ""; 
            });
        }

        // Background color picker event / 글자 배경색 이벤트
        const backColorPicker = this.toolbar.querySelector('#asamiya-backcolor');
        if (backColorPicker) {
            backColorPicker.addEventListener('input', (e) => {
                if (this.isHTMLMode) return;
                this.contentArea.focus();
                // Use hiliteColor (Chrome) or backColor (Firefox) depending on browser
                document.execCommand('hiliteColor', false, e.target.value);
            });
        }

        // Text color picker event / 글자 색상 이벤트
        const colorPicker = this.toolbar.querySelector('#asamiya-forecolor');
        if (colorPicker) {
            colorPicker.addEventListener('input', (e) => {
                if (this.isHTMLMode) return;
                this.contentArea.focus();
                document.execCommand('foreColor', false, e.target.value);
            });
        }

        // Update toolbar state (Highlight active buttons based on cursor position)
        this.contentArea.addEventListener('keyup', () => this.updateToolbarState(buttons));
        this.contentArea.addEventListener('mouseup', () => this.updateToolbarState(buttons));
        this.contentArea.addEventListener('click', () => this.updateToolbarState(buttons));
    }

    updateToolbarState(buttons) {
        buttons.forEach(btn => {
            const command = btn.getAttribute('data-command');
            if (['bold', 'italic', 'underline', 'strikeThrough', 'justifyLeft', 'justifyCenter', 'justifyRight'].includes(command)) {
                if (document.queryCommandState(command)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            }
        });
    }

    // Image file picker with server upload support (Fallback to Base64 if no upload URL is defined)
    handleImageUpload() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;

            // Check if upload URL is defined globally (e.g. from Gnuboard plugin)
            if (typeof asamiyaEditorUploadUrl !== 'undefined' && asamiyaEditorUploadUrl) {
                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(asamiyaEditorUploadUrl, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (result.error) {
                        alert(result.error);
                        return;
                    }
                    if (result.url) {
                        this.contentArea.focus();
                        document.execCommand('insertImage', false, result.url);
                        return;
                    }
                } catch (error) {
                    console.error('Image upload failed:', error);
                    alert('이미지 업로드 중 서버 통신 오류가 발생했습니다.');
                    return;
                }
            }

            // Fallback to Base64 if no upload URL
            const reader = new FileReader();
            reader.onload = (e) => {
                const base64Image = e.target.result;
                this.contentArea.focus();
                document.execCommand('insertImage', false, base64Image);
            };
            reader.readAsDataURL(file);
        };
    }

    // External API Methods / 외부용 API 메서드
    getHTML() {
        if (this.isHTMLMode) {
            return this.htmlArea.value;
        }
        return this.contentArea.innerHTML;
    }

    setHTML(html) {
        if (this.isHTMLMode) {
            this.htmlArea.value = html;
        } else {
            this.contentArea.innerHTML = html;
        }
    }

    // Handle Table Insertion / 표(Table) 삽입 처리
    handleTableInsert() {
        const rows = prompt('몇 줄(행) 짜리 표를 만들까요?', '3');
        const cols = prompt('몇 칸(열) 짜리 표를 만들까요?', '3');
        
        const rowCount = parseInt(rows, 10);
        const colCount = parseInt(cols, 10);

        if (isNaN(rowCount) || isNaN(colCount) || rowCount <= 0 || colCount <= 0) {
            alert('올바른 숫자를 입력해 주세요.');
            return;
        }

        let tableHTML = '<table class="asamiya-table"><tbody>';
        for (let r = 0; r < rowCount; r++) {
            tableHTML += '<tr>';
            for (let c = 0; c < colCount; c++) {
                if (r === 0) {
                    tableHTML += '<th>제목</th>';
                } else {
                    tableHTML += '<td>내용</td>';
                }
            }
            tableHTML += '</tr>';
        }
        tableHTML += '</tbody></table><p><br></p>';

        this.contentArea.focus();
        document.execCommand('insertHTML', false, tableHTML);
    }

    // Handle YouTube Embed / 유튜브 임베드 처리
    handleYoutubeEmbed() {
        const url = prompt('유튜브 영상 URL을 입력하세요:', 'https://www.youtube.com/watch?v=...');
        if (!url) return;

        // Extract YouTube ID (Regex) / 유튜브 ID 추출 (정규식)
        const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&]{11})/);
        
        if (match && match[1]) {
            const videoId = match[1];
            const iframeHTML = `<br><iframe width="560" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><br>`;
            
            this.contentArea.focus();
            document.execCommand('insertHTML', false, iframeHTML);
        } else {
            alert('유효한 유튜브 URL이 아닙니다.');
        }
    }

    // Toggle HTML Mode / HTML 모드 토글
    toggleHTMLMode(btn) {
        this.isHTMLMode = !this.isHTMLMode;

        if (this.isHTMLMode) {
            // Enable HTML mode / HTML 모드 켜기
            this.htmlArea.value = this.contentArea.innerHTML;
            this.contentArea.style.display = 'none';
            this.htmlArea.style.display = 'block';
            btn.classList.add('active');
        } else {
            // Disable HTML mode (Return to WYSIWYG) / HTML 모드 끄기 (WYSIWYG로 복귀)
            this.contentArea.innerHTML = this.htmlArea.value;
            this.htmlArea.style.display = 'none';
            this.contentArea.style.display = 'block';
            btn.classList.remove('active');
        }
    }
}
