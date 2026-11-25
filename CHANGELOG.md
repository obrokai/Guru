# Guru 主題 — 版本紀錄

版本號記在 `guru-theme/style.css` 的 `Version:` 標頭（後台 外觀 → 佈景主題 會顯示），
安裝檔檔名為 `guru-theme-v<版本>-<日期>.zip`，例如 `guru-theme-v1.2.0-20260911.zip`。
壓縮檔內的資料夾一律是 `guru-theme`，所以上傳新版時 WordPress 會問「要用上傳的版本取代嗎」，選取代即可。

打包指令：`tools/package.sh`（會自動讀版本、加上今天的日期）。

---

## 1.4.3 — 2026-11-25

- **後台顯示主機硬碟空間**：工具 → 網站狀態 → 資訊 新增「Guru server」區塊（剩餘空間、總容量、使用率），只有管理員看得到、只讀。備份與測試站搬到正式站都會在同一顆硬碟寫入數 GB，動手前先看這裡。

## 1.4.2 — 2026-11-25

客戶最新六項清單中可直接做的部分（語言、文章作者、相關服務／作品、標題大小）。

- **語言只有一個來源**：前台用英文 locale（`switch_to_locale('en_US')`，只在前台，後台維持中文），`<html lang>` 為 en-HK；Yoast Schema 的 `inLanguage`（原本 zh-TW）與 Open Graph `og:locale` 也改 en-HK／en_HK。裝了多語外掛時不介入。
- **文章作者**：作者有公開名稱就用該作者（名稱＋個人簡介），否則顯示「GURU Editorial Team」與簡介（Customize → Insights articles 可改）。標題下「By …」、內文後作者欄（Written by／簡介／Published 日期），Yoast Schema 的 author、`<meta name="author">`、Slack 分享的 Written by 都用同一份資料，不再出現 admin。不顯示「更新日期」（匯入文章的修改日期都是匯入當天）。
- **相關服務／作品＋詢問按鈕**：文章編輯畫面右側新增「Related services & work」，勾選要連結的服務與作品；文章底部顯示為一般 `<a href>` 連結（服務連到 Solutions 頁對應區塊、作品卡片連到作品頁），再加一個詢問區塊（標題、說明、按鈕文字在 Customize → Insights articles 可改，按鈕跳到頁尾聯絡表單）。沒勾就不顯示相關區。
- **About 標題一行**：「ABOUT GURU」不再每個字換行；窄手機仍會自然換行。
- **Insights 標題改回與 About／Work 同大小**（`.blg-hero-title`，Customize → Typography 的 Hero 比例同樣適用）。
- 檢查：主題內沒有寫死 /staging/ 或網域的連結，全部用 WordPress 網址函式。

## 1.4.1 — 2026-11-18

修 1.4.0 驗收看到的小地方，加上作品卡片「點了才載入影片」（規格 #4）。

- **作品卡片點了才載入影片**：有封面圖（精選圖片）的影片卡片只顯示封面，不下載影片；點卡片才在燈箱載入播放。沒有封面的維持「捲到附近才播放預覽」。staging 10 個影片作品已用影片截圖補上封面（資料調整，不在主題內）。
- **Investor 與 Insights 標題左緣對齊**：Investor 改用全站共用的內容框（左右 40px），內容仍 1160 寬、靠左；手機左右 22px。
- **Insights 摘要太短**：中文介面下 WordPress 以字元計算摘要，英文只剩二十幾個字。新增 `guru_card_excerpt()`：英文約 200 字元、斷在完整單字；中文 96 字；CSS 最多 3 行，日期列對齊卡片底部。
- **文章頁手機多 3px**：內文長網址可換行（`overflow-wrap: break-word`）；清單圓點縮排到文字欄內。
- **手機減少動態預覽圖**：`hero-poster-mobile.jpg` 改 1280×1280，G 標誌移到上方約 21%，各手機尺寸都露在標題卡上方。

## 1.4.0 — 2026-11-18

客戶 11-17《Website 2.0 Programmer 執行規格》第二批中不需要客戶資料的項目：#4、#5、#7、#8、第 6.2／6.3 節版面，以及第 9 節的表單、404、作者。GA4（#9）、公司資料（#14）、內容（#15）、Search Console（#16）、手機影片壓縮（#6，要換 staging 媒體）等客戶資料。

- **#4 首頁影片預覽圖**：新增 `assets/media/hero-poster.jpg`（1920×1080，18 KB）、`hero-poster-mobile.jpg`（720×1280，9 KB），取自影片 42 秒的綠底 GURU logo 畫面；Customizer → Homepage 新增「Hero still picture (desktop／mobile)」可換。影片檔不寫在 HTML，由 `front-page.php` 緊接在 video 後的小段程式掛上（`source[data-src]`），`preload="metadata"`。沒有 JavaScript、自動播放被擋（`play()` 被拒）或影片載入失敗時顯示預覽圖。正常播放時不先顯示預覽圖（影片第一格是白的，避免綠色閃一下）。
- **#4 作品卡片**：首頁與 Work 頁有影片的卡片加 `poster`（精選圖片）。
- **#5 減少動態效果**：使用者開了 `prefers-reduced-motion` 時，首頁影片不下載、不播放，只顯示預覽圖；開著頁面時改設定會暫停，改回會播放。作品卡片影片不載入；Solutions 頁影片不自動播放，改成有播放控制列。
- **修正：開了減少動態效果的人整頁空白**。開場遮罩 `.pg-intro` 的淡出動畫被關掉後就一直蓋著；現在這種情況下直接不顯示（style.css 一條規則，涵蓋所有頁）。
- **#7 不載入 Contact Form 7**：staging 每頁都載入 CF7 的 css、js 及 wp-hooks／wp-i18n，但表單都是主題自己的。`wpcf7_load_js`／`wpcf7_load_css` 只在頁面真的有 CF7 區塊時才回 true。
- **#8 鍵盤與讀屏**：
  - 手機選單：打開時焦點到第一個連結，Tab 只在選單按鈕與選單之間循環，Esc 關閉並回到選單按鈕；選單按鈕加 `aria-controls`。
  - 作品燈箱：`role="dialog"`、`aria-modal`、以作品標題命名；Tab 只在燈箱內循環（`guruTrapTab()`）。
  - 修正：關著的燈箱只是透明，Tab 仍會走到看不見的關閉鈕和表單；現在淡出後 `visibility: hidden`。
- **表單（#8、第 9 節）**：
  - 電話改選填（前端、後端都改）；有填時仍檢查位數。
  - 四個表單共用 `guruCheckForm()`：一次列出所有問題（「Please check: Name is required; …」），`role="alert"` 讓讀屏讀出，問題欄位標 `aria-invalid`，游標移到第一個。
  - 每個表單送出鈕上方加一行私隱說明（`guru_form_privacy_note()`）；後台「設定 → 隱私權」有設私隱政策頁時附連結。文字待 GURU 確認。
  - 每個欄位加看得見的 `<label class="guru-label">`（12px 粗體大寫，必填藍色 *、選填「(optional)」），拿掉重複的 aria-label；Email placeholder 改範例、服務下拉第一項改「Select a service」；加 `autocomplete`；`aria-invalid` 欄位紅框（#c0392b）。燈箱表單錯誤字由 #ff6b6b 改 #c0392b（淺色卡片上 2.72 → 5.34:1）。
  - `tools/test-form.py` 加兩項：沒填電話要成功、沒填公司要失敗，七項全過。
- **文章**：日期一律英文（「17 Nov 2026」，`<time datetime>`），原本跟著後台語言顯示成「10 11 月, 2026」；作者是 admin、或沒設公開名稱（顯示名稱＝登入帳號）時不顯示（規格 #15：不顯示 admin）。`guru_post_date()`、`guru_post_author()`。
- **6.3 Insights 與 Investor 標題統一**：共用 `.page-title`（`clamp(44px, 3.7vw, 52px)`、700、行高 1.05、字距 -0.03em，≤767px 36px），1440 寬時兩頁都是 52px（原本 115px／86px）。字級工具把 `page-title` 歸在「Page titles」，兩頁一起縮放。
- **Insights 頁**：標題區高度隨內容；選單底到標題 72px、標題底到分隔線約 64px；文章格子 3／2／1 欄、圖片 16:9；手機篩選列可橫滑且不撐寬頁面。
- **Investor 頁**：公司名稱不再強制換行（1440 一行，窄螢幕自然換行）；主內容 1160px、介紹 1040px；H1 到介紹 28px、介紹到資料區 84px、文件欄與聯絡卡間距 64px；1440×900 首屏可同時看到標題、介紹、第一筆文件和聯絡卡頂部。文件按鈕顯示語言、PDF 和檔案大小（「English PDF · 412 KB」），讀屏名稱含文件名；大小先讀附件資料，再讀伺服器上的檔案，都沒有就不顯示。列表快取鍵改 `_v2`。
- **6.2 首頁 Our Solutions**：上下 72px、左右 40px、欄寬約 0.9／1.2、欄距 72px、eyebrow 到標題 32px、手風琴頂部與標題頂部對齊；「Our Solutions」改 6vw 保持一行；手機上下 40／48px、左右 20px、兩部分間距 32px。

## 1.3.3 — 2026-11-18

修 1.3.2 驗收找到的問題。

- **首頁霧面卡片在全黑畫面上不再變灰**（影片約 6 秒、10.5 秒的「Beyond service menu」黑畫面）。`main.js` 每 0.4 秒取樣卡片後方影片的亮度（8×4 小圖）：畫面越暗，卡片白色由 58% 平滑加到 88%（`--gh-glass-a`，0.8 秒漸變）；亮、綠、彩色畫面維持 58%，透明感不變。影片讀不到時（跨網域）自動停用、維持 58%（內文仍 4.6:1）。實測全黑畫面：標題 6.4 → 14.6:1、內文 4.6 → 10.5:1。減少動態效果時不做漸變。
- **黑色「Discuss Your Growth Challenge」按鈕在黑底上看得到邊**：加 1px、26% 白的細框（白按鈕淡框的反轉版），白底上看不到、綠底上很淡。
- **Solutions 手機版內容被切到右邊**：手機版欄位改 `minmax(0, 1fr)`、`.srv-main` 加 `min-width: 0`。原本三張手機截圖那一排（414px）把整頁撐到 454px。375／390／414／768 寬都無橫向捲動。
- **Solutions 手機版「OUR SOLUTIONS」被 GURU logo 蓋住**：頁面載入時不再自動往下捲。點服務按鈕或「Next」時，只有內容區頂端已經捲出畫面才捲動，而且捲回最上方。

## 1.3.2 — 2026-11-18

- **首頁標題區改成 Apple 風格的透明霧面卡片**（Mike：原本標題後面的黑底太突兀，要融入現有設計、要透明）。
  - 標題＋副標題放在一張霧面玻璃卡片裡（`.gh--glass`）：白色只疊 58%，後方影片模糊 32px、飽和 180% 透出來；1px 半透明白框＋上緣細亮光＋柔和陰影（與 menu 同款淡細框，白畫面也看得出邊）；圓角 36px（手機 26px）。
  - 字改成跟 menu 一樣的深色（標題 `--ink`、副標題 `--ink-2`），整支影片（全白、暗色賓士、綠底、拼貼、派對）實測最差：標題 6.7:1、副標題 4.9:1，都過規格 4.5:1。不支援 backdrop-filter 的瀏覽器退回 90% 白底。
  - 版面：寬螢幕（≥1280 寬且 >700 高）標題卡片在左下、三顆按鈕在右下，兩組底部對齊；其他尺寸按鈕排在卡片下方。卡片左緣對齊左上 logo 膠囊，按鈕右緣對齊右上 menu 膠囊。白按鈕加 menu 同款 1px 淡框。
  - 拿掉 v1.3.0 的局部深色背板（`.gh-copy::before`）和底部全寬陰影（`.gh::after`）。
  - 設計過程的其他版本都留在 style.css（v1.3.0 hero 區塊），改 `front-page.php` 上 `section.gh` 的 class 即可切換：`gh--pill`（94% 白卡）、`gh--glass-dark`（深色霧面＋白字）、`gh--pill-split`（兩顆膠囊）、`gh--nobg`（無底色、字邊光暈）、`gh--g-a/b/c`、`gh--legible`（全寬綠底，深淺不同）。
- **首頁副標題縮短**（Mike）：預設文字改成「Strategy, creative, media and technology that help brands enter markets and build demand.」（Customizer `guru_hero_intro`）。後台若已存過自訂文字，以後台為準。
- 新增設計 subagent 定義 `~/.claude/agents/guru-design.md`（不在主題內）。

## 1.3.1 — 2026-11-18

- **有影片也有圖片的作品：影片和圖片放在同一個輪播**（客戶：「如果又有 video 又有圖片的話，它好像只可以 show 到 video，不能有圖片」；Mike：影片＋圖片也用 slide）。staging 上「留心蛋 Mr. Safegg」就是這種（影片＋精選圖片＋內文 3 張圖）。
  - `guru_get_work_data()` 的 `gallery` 不再因為有影片而清空；首頁與 Work 頁的影片卡片也帶 `data-gallery`。
  - 燈箱：有影片又有圖片時，影片變成輪播第一張（自動播放），後面接圖片；切到別張時影片暫停，關燈箱時停止並清空。只有影片、或只有圖片的作品維持原樣。影片那一張不能用滑鼠拖曳換頁（保留影片進度條操作），用箭頭、圓點、鍵盤或手機滑動。
  - 作品頁 `single-work.php`：同樣用一個輪播（影片在前、精選圖片當封面），內文的圖片移到輪播裡。
  - `guru_gallery_markup()` 支援影片項目；`guruGallery().set()` 支援 `{v: 影片網址}`；`.gg-slide video` 黑底、contain。

## 1.3.0 — 2026-11-18

客戶 11-17 的《Website 2.0 Programmer 執行規格》第一批（#1–#3、#10–#13、首頁 H1、導覽與表單）。客戶已在 staging 裝好 Yoast SEO，主題不輸出任何 title／description／canonical／robots／schema，全部交給 Yoast。

- **#1 標題階層**：
  - 首頁新增唯一 H1（`.gh-title`，左下、近白 alpha .92、桌機 36–44px、手機 30–36px），下方 intro 一句與三個按鈕（Discuss Your Growth Challenge → `#contact`、Our Work、Our Solutions）。文字都在 Customize → Guru site settings → Homepage（新欄位 `guru_hero_title`／`guru_hero_intro`／`guru_hero_cta_*`），預設值為規格的英文草稿，待 GURU 確認。舊的「Our Works／Our Services」兩顆按鈕由這三顆取代。
  - 文字後方加局部深色漸層（`.gh-copy::before`，跟著文字區塊，右側 420px、上方 120px 漸淡），白色影格上白字對比約 6:1（規格要求 4.5:1）。影片加 `aria-hidden`。
  - 視窗 ≤767px 寬或 ≤700px 高時 hero 不再限制高度，文字不會被裁掉。
  - 首頁 Our Solutions 手風琴每列改成 `<h3><button aria-expanded>`。
  - Solutions 頁：側欄標籤改成頁面唯一 H1（Customizer `guru_solutions_heading`，預設「Our Solutions」，手機也顯示在膠囊列上方）；九個服務標題由 H1 改 H2。
- **#2 Main／Skip link**：`header.php` 開 `<main id="main-content" tabindex="-1">`，`footer.php`／`footer-minimal.php` 關閉；選單與頁尾在 main 外。Solutions 頁原本的 `<main class="srv-main">` 改 div（id 不變）。第一個 Tab 出現「Skip to main content」，按下後焦點移進 main。
- **#3 語言**：新檔 `inc/seo.php`。前台 `<html lang>` 改 `en-HK`（後台仍是繁中）；Yoast `og:locale` 跟著改（Yoast 不認得 en_HK，會輸出 en_US）。之後裝多語外掛時自動讓出。
- **#10／#12 SEO**：主題沒有寫死 `/staging/`。Yoast 沒填 description 時，用自動描述補上（首頁＝hero intro、Solutions＝intro、Investor＝介紹、Work 列表＝固定一句、文章／作品＝摘要或內文開頭，作品沒內文時＝「標題 — a project for 客戶」）。沒有任何 SEO 外掛時主題才自己印 description。Investor 完整列表（`?docs=…`）設 `noindex, follow`。
- **#13 可爬取連結**：首頁與 Work 頁的作品卡片標題改成真正的 `<a href="作品頁">`，播放鈕改 `<button>`；點擊行為不變（有影片／圖片開燈箱）。卡片因此可以用鍵盤操作（原本只能用滑鼠）。
- **燈箱**：開啟時焦點移到關閉鈕，Esc 關閉後回到原本的卡片。
- **表單**：只有在「存進後台」或「寄出 email」至少一項成功時才顯示成功，否則顯示錯誤（以前一律顯示成功）。送出後按鈕鎖住，避免連按重複送出；用瀏覽器返回時解鎖。

本機測試（WordPress 7.1＋Yoast 28.8）：六頁各一個 H1、一個 main、lang=en-HK；Yoast 的 title／description／canonical／robots／JSON-LD 各一份、無重複；手機 375px 無橫向捲動；`tools/test-form.py` 五項全過。

## 1.2.16 — 2026-11-17

- **選單改白色**（客戶 Fontane：「Menu bar 的顏色麻煩調校跟背景白色一樣，現在是米黃色」；Mike：加一點陰影，不然跟底部看不清）。`.gn-logo`／`.gn-pill` 由 `rgba(247,244,232,.92)` 改 `rgba(255,255,255,.94)`，陰影改 `0 2px 14px rgba(14,14,16,.10), 0 0 0 1px rgba(14,14,16,.05)`；手機選單面板 `.gn-mobile-menu` 改 `rgba(255,255,255,.97)`。六個樣式檔（style、about、blog、work、services、investor）都有自己的一份，全部同步。Customizer「Menu background」預設值改 `#ffffff`（staging 沒存過這個值，上傳即生效）。
- **手機首頁間距縮小**（≤980px）：`.svc-label` margin-bottom 56 → 20px；Solutions 與 Our Work 之間 160 → 80px（`.svc` padding-bottom 40px、`.wrk` padding-top 40px）。電腦版不變。

## 1.2.15 — 2026-11-17

- **圖片作品改成跟影片一樣的瀏覽方式，多張圖可左右切換**（客戶：Construction Industry Council 是上傳圖片，呈現不好看，希望像影片那樣；Mike：多張圖可以左右 slide，手機也要兼顧）。
  - 沒有影片的作品，圖片取自精選圖片（第一張）＋內文中所有 `<img>`（依出現順序；有 `wp-image-N` 就用附件的 1536 尺寸，同一附件只算一次）。新函式 `guru_work_gallery()`，`guru_get_work_data()` 多一個 `gallery`。
  - 卡片（首頁 `front-page.php`、Work 頁 `guru_work_card()`）多帶 `data-gallery`（JSON），點了開燈箱而不是跳頁；沒影片也沒圖片的仍然跳到作品頁（首頁的圖片卡片原本開空燈箱，現在也一樣跳頁）。
  - 兩個燈箱（`work-modal.php`、`work-modal-archive.php`）在影片下方加輪播 `guru_gallery_markup()`；`main.js` 新增 `guruGallery()`：CSS scroll-snap（手機原生滑動）、左右箭頭、鍵盤 ← →、滑鼠拖曳、圓點；關燈箱會清空。開表單時圖片框縮到 38vh，跟影片一致。
  - 作品頁 `single-work.php`：有圖片就顯示同一個輪播，內文用 `guru_strip_content_images()` 拿掉圖片（只剩圖片時整段不顯示）。
  - 樣式在 `style.css`（`.guru-gallery`、`.gg-*`）：電腦 16:9、黑底、圖片 contain；手機（≤700px）正方形框、隱藏「2 / 4」計數；圓點放在圖片下方的黑條，不蓋住圖。
  - 影片作品不受影響。

## 1.2.14 — 2026-11-08

- **首頁跑馬燈的 Client logo 放大並修飾**（Mike：「好像很小，跟上一版一樣寬，也要兼具美觀」）。Client logos 是 480px 正方形、白底、上下約 19% 留白，原本塞在 52px 高的格子裡只剩約 30px 的 logo，還被濾鏡壓成灰色方塊。現在（只在顯示 Client logos 時，`.logo-ticker--clients`）：
  - 每個 logo 顯示 150px 寬（約等於上一版字標的寬度），上下多餘留白裁掉，整條高度 124px（上一版 102px）；手機 120px 寬、整條 105px。
  - 白底用 `mix-blend-mode: multiply` 融進頁面背景，看不到方塊；灰階＋半透明改由圖片自己的 filter 處理（item 不能再有 opacity，否則會隔離混合、白底又出現），滑鼠移上去恢復原色。
  - 沒有 Client logo 時顯示的原設計字標不受影響。

## 1.2.13 — 2026-11-08

- **Investor 頁接上真正的投資人文件**（客戶要求「新增 announcement，參考原本網站」）。資料就是原網站後台「Investor Doc」裡的文件（`investor_document`，正式站與測試站約 1,045 筆，由 Pods 註冊；ACF 欄位 `date_of_publish`＝Ymd、`document`＝PDF 附件 ID；分類 `document_category`：`financial-information`、`last-announcement`）。新檔 `inc/investor-docs.php`。
  - Investor 頁分兩區：**Financial Information**、**Last Announcement**，各顯示最新 6 列，下方「View All …」按鈕；其後保留 HKEXnews 連結與 Corporate Governance。
  - **完整列表**在同一頁：`?docs=last-announcement`（或 `financial-information`），每頁 20 列、可翻頁（`&pg=2`），上方「← Investor Relations」返回。用網址參數而不是新頁面或分類網址，測試站的 `?page_id=` 網址與正式站的漂亮網址都能用。
  - **中英文合併成一列**：同一天發佈、ID 相鄰的一中一英配成一列（中文以 PDF 檔名結尾 `_c`／`_c-1` 或標題含中文字判斷），英文標題為主、中文標題在下，附 EN／中文按鈕；落單的單獨一列。
  - 列表結果快取一天；新增、修改、刪除、改分類任何一份文件都會立刻清掉快取。
  - 客戶新增公告的方式不變：後台「Investor Doc」照原本新增即可。
  - 還沒有任何文件時（新安裝），沿用 Customizer 的「Financial results & reports」清單（欄位改名為 fallback 並加說明）。
  - 舊網址 `/last-announcement/`、`/financial-information/`、`/document-category/…` 改為 301 到對應的完整列表（原本只到 Investor 頁）。
  - 主題只在沒有外掛註冊時才自行註冊 `investor_document` 與 `document_category`。

## 1.2.12 — 2026-11-08

- **首頁載入卡頓**（客戶回報）：作品卡影片不再一打開頁面就全部下載、播放；改為捲到畫面附近（上下 200px）才載入並播放，離開畫面就暫停（`video[data-lazy-play]`、`main.js`）。首頁「Our Work」與 Work 頁的作品卡都適用；燈箱、Solutions 頁影片不變。主要原因其實是客戶 10 月換上的主視覺影片是 4K 原檔（電腦 285.8 MB、手機 257.7 MB），已另外壓成網頁版（9.0 MB／4.5 MB）並換到測試站（資料調整，不在主題內）。
- **首頁跑馬燈改為每次隨機 8 個 Client logo**（客戶要求）：從所有有圖片的 Client logos 隨機抽，不必再逐一勾選；Client logos 編輯畫面的「Homepage ticker」勾選框與列表欄位一併拿掉。數量在 `template-parts/logo-ticker.php` 的 `$guru_ticker_count`。還沒有任何 Client logo 時，仍顯示原設計的六個品牌。

## 1.2.11 — 2026-11-06

- **辦公室資料更新為客戶 11/06 提供的版本**（About「Our Offices」與 Investor「IR & Office Contacts」）：香港（總部）、澳門、廣州、南京、台北、新加坡，每間都有 E-mail 與地址。Investor 拿掉北京、原本的電話改成 E-mail。
- **兩處都支援 E-mail**，顯示成可點的 mailto 連結：
  - About 卡片格式改為「城市 | E-mail | 地址 | 標籤（選填，有標籤＝大卡）」，地址與 E-mail 一起排在卡片底部（`.abt-office-contact`、`.abt-office-email`）。
  - Investor 格式改為「城市 | E-mail | 地址」。
  - 兩處都相容舊格式（第二欄沒有 @ 就照舊當地區／電話顯示）。
  - 手機（≤520px）上辦公室卡片改為一欄：兩欄時卡片太窄，E-mail 會被斷在字中間。E-mail 只在真的放不下時才換行（`overflow-wrap: anywhere`）。
- Customizer 欄位說明同步；新安裝的預設值也換成新資料（`GURU_OFFICES_DEFAULT`、`GURU_INVESTOR_CONTACTS_DEFAULT`）。
- `tools/build-typography.py` 的內文類別加入 `-email`，客戶調內文字級時 E-mail 跟著縮放；`tools/parity-check.py` 的 Investor 聯絡資料預期 5 → 6。

## 1.2.10 — 2026-11-06

客戶回覆要刪除的三處（都是刻意偏離 Vercel 靜態版）：

- **Investor 頁右側拿掉「Listing Information」與「Useful Links」兩張卡片**，側欄只留「IR & Office Contacts」。Customizer → Investor page 一併拿掉只給這兩張卡用的 5 個設定（Exchange、Board lot、Fiscal year end、Stock quote link、Previous IR website link）；Stock code 保留（頁尾免責聲明那行會用），「View all announcements」連結保留（文件清單用）。`tools/parity-check.py` 的側欄卡片預期 3 → 1。
- **Blog 頁「Insights」旁邊的介紹文字拿掉**；Customizer 的「Blog」區塊（只有這段文字）一併拿掉。404 頁仍保留「找不到頁面」的說明。
- **Work 頁「Work」旁邊的作品數量（例如「17 Projects」）拿掉**。篩選分類時更新數量的 JS 本來就會先檢查元素是否存在，不需修改。

## 1.2.9 — 2026-11-06

- **修正：Investor 頁出現一大段「亂碼」**（客戶回報）。那其實是舊網站用 Visual Composer／Elated 排版留下的版面代碼（`[vc_row …]`、`[eltd_elements_holder …]`），本主題不會轉成版面，於是原樣印成文字。一般頁面（`page.php`）本來就會先清掉這類代碼，Investor 模板（`page-investor.php`）沒有；現在兩者共用 `guru_clean_page_content()`（`inc/template-helpers.php`）。
- 測試站同時做了**資料調整（不在主題內）**：導覽列的 Investor 連到的是舊站頁面（ID 14094，網址 `investor`），我們匯入的「Investor Relations」（ID 24394）被擠成 `investor-2`。已把舊頁改為草稿、網址改成 `investor-old`，新頁接手 `investor`。這原本是匯入工具步驟 2 該做的事；沒有重跑步驟 2，因為它也會重設首頁、網誌頁與選單。

## 1.2.8 — 2026-10-23

客戶群組 10/15～10/20 提出的問題（`~/Downloads/IMG_9932.JPG`）。

- **首頁「Your Holistic Digital Partner」移到「Our Solutions」上面，並縮小**（客戶要求）。順序變成：主視覺影片 → 客戶 logo → About → Our Solutions → Our Work。區塊上下留白 120px → 72px、標題最大 90px → 56px、數字最大 88px → 52px，桌機四個數字排成一列；區塊高度約 425px，在 1440×900 的螢幕上捲到這裡時，一個畫面能同時看到公司介紹與 Our Solutions 前三項。手機同步縮小（留白 56px、標題最大 44px），但手機畫面窄，About 本身約一個螢幕高。這是刻意偏離 Vercel 靜態版的一處。
- **首頁主視覺影片只播一次**（客戶要求）。拿掉 `loop`，播完停在最後一格。
- **頁尾拿掉「Designed with ♥ in Hong Kong」**（客戶要求）。
- **新增「選單顏色」設定**：外觀 → 自訂 → Guru site settings → Menu colours，可改選單底色、選單文字、「Contact Us」按鈕、「WhatsApp」按鈕四個顏色（電腦版上方選單與手機選單一起變）。沒改的顏色不輸出任何 CSS，網站外觀與之前完全相同。新檔 `inc/menu-colors.php`。
- **「編輯者」帳號也能進「外觀 → 自訂」**（客戶回報側邊欄沒有「外觀」）。客戶日常用的帳號是編輯者，而所有網站設定都在自訂裡。只在使用本主題時生效（不改資料庫裡的角色）；匯入工具、換主題、外掛等仍然只有管理員能用。
- **作品／Solutions 編輯畫面的「Showcase details」「Solution media & highlights」永遠展開**（客戶回報「上傳影片的欄位不見了」）。實際是欄位框被點到標題而收合，只剩一條標題列，WordPress 會記住收合狀態。現在這兩個框拿掉收合與「顯示項目設定」裡的隱藏選項，之前存下的收合／隱藏狀態也一併忽略。

## 1.2.7 — 2026-10-15

- **首頁「Our Solutions」改成滑鼠移過去就展開**（客戶要求）。原本要先點名稱展開簡介，再點「Explore →」才會到 Solutions 頁，總共點兩次。現在用滑鼠的電腦上，**移到哪一列就展開那一列的簡介**（快速掃過不會每列都打開，只開停下來的那列），**點名稱一次就直接跳到那個 solution**；展開時右側箭頭從「⌄」改成「→」，提示可以點。手機與平板沒有滑鼠移過去這個動作，維持原本做法：點一下展開簡介，再點「Explore →」前往。改動在 `assets/js/main.js` 與 `style.css`。
- **修正：手機上「Our Solutions」展開後看不到「Explore →」**。展開區高度上限是 240px，但測試站的簡介在手機寬度要 270～323px，超出的部分（包括 Explore 連結）被切掉，手機使用者無法前往 Solutions 頁；電腦 1024px 寬時也會切到一點。上限改為 480px。

## 1.2.6 — 2026-09-22

- **首頁「Our Work」位置重號**：客戶把「Test」的 Homepage position 填 1，首頁卻排在第 2 格。原因是匯入的四個作品本來就佔 1～4（MGTO × CreatorWeek = 1），兩個「1」同分時舊的排前面。修正：儲存作品時**一個數字只有一個作品**——其他佔同一數字的作品會自動清掉位置（`guru_claim_home_slot()`），並在編輯畫面上方提示「第 N 格原本是 XXX，已移出首頁」；首頁查詢加上「同分時最近更新的優先」當保險。欄位說明、README、客戶教學同步改寫（不再要客戶手動把舊作品留空）。
- 客戶教學新增 **PDF 版**（`docs/客戶教學-上架作品與調整字體.pdf`，A4 直式 8 頁，截圖內嵌，給打不開 HTML 的客戶），由 `tools/guide-pdf.mjs` 透過 headless Chrome 的 DevTools protocol 從 HTML 版列印；HTML 改了就重跑。`tools/guide-template.{html,md}` 同步到 v1.2.6。

## 1.2.5 — 2026-09-22

- **Work 頁所有卡片改為一致的 4:3**（客戶要求）。靜態版原設計每第五張卡（第 1、6、11 張）是較矮的 16:9「large」變體，客戶上傳的「Test」剛好是第 1 張，看起來比旁邊兩張矮。客戶希望所有封面一樣大、畫面取中間，因此拿掉 16:9 變體：`work.css` 移除 `.prj-card.large` 規則，`guru_work_card()` 不再輸出 `large` class，`tools/parity-check.py` 同步移除該項預期。圖片裁切由 `object-fit: cover` 取正中央，不需另外設定。這是刻意偏離 Vercel 靜態版的一處。
- **About 頁的世界地圖影片移除**（客戶要求）。辦公室區塊上方原本有一支 21:9 的世界地圖影片；`template-parts/offices.php` 不再輸出，Customizer「About page → World map video」選項移除，`about.css` 相關規則刪除，`assets/media/world-map.mp4`（1.7 MB）從主題拿掉（仍在 git 歷史）。辦公室標題與卡片之間保留原本 48px 間距。同樣是刻意偏離靜態版。
- **全站規則：上傳的封面圖／影片一律填滿框、取正中央裁切**（客戶要求）。盤點主題所有顯示上傳媒體的位置，補齊漏掉的三處：Solutions 輪播的圖片（原本只有影片有規則）、Blog 文章頁的精選圖片（改為 16:9 框）、作品單頁的封面圖（改為 16:9 框）。原本就正確的：首頁與 Work 頁卡片、首頁 hero 影片、Blog 卡片、Solutions 單支影片。刻意不裁的：燈箱與作品單頁的影片本身（要看完整影片）、Solutions 手機截圖（可上下滑的長圖）、客戶 logo（logo 不能裁）。

## 1.2.4 — 2026-09-22

- **Work 頁版面被封面圖撐壞**：客戶第一次上傳封面圖後，整頁變成「一大兩小」、右邊兩欄擠成小方塊。原因是卡片內的 `<img>` 沒有填滿規則，1024px 原圖把 grid 欄位撐開（靜態站的卡片只有色塊與影片，從未有真的圖片，所以這條規則一直沒被需要）。補上與影片相同的 `position:absolute; inset:0; object-fit:cover`，並讓卡片 `min-width:0`。首頁「Our Work」四格同樣補上，避免客戶把有圖的作品放上首頁時出現同樣狀況。

## 1.2.3 — 2026-09-22

- **修正 1.2.1／1.2.2 的字級錯誤（重要）**：Typography 功能在預設 100% 時沒有定義 CSS 變數，`calc(… * var(--guru-fs-…))` 因此整條失效，各頁大標題、區塊標題退回繼承的內文字級（About 頁「ABOUT GURU」縮成 16px）。產生器改為 `var(--guru-fs-…, 1)` 帶預設值；本機以算出的字級驗證（About 大標題 115px、內文 17px，與設計相同）。**若已安裝 1.2.1 或 1.2.2，請務必更新到這版。**
- 客戶教學文件加入後台實際畫面截圖（7 張，繁體中文介面、紅框標示），`docs/客戶教學-上架作品與調整字體.html` 與 `docs/images/`。

## 1.2.2 — 2026-09-15

- **Showcase details／Solution media 欄位移到標題正下方、內文編輯器之上**。1.2.1 改用傳統編輯畫面後，欄位仍排在內文編輯器下面（WordPress 的預設位置），要往下捲才看得到；現在打開編輯畫面第一眼就是這些欄位。
- 新增客戶教學文件 `docs/客戶教學-上架作品與調整字體.md`。

## 1.2.1 — 2026-09-15

客戶在測試站上架作品時找不到欄位、上傳失敗（客戶回饋 9/15）。

- **Showcase 與 Solutions 改用傳統編輯畫面**（不再是區塊編輯器）。原本「Showcase details」被擠到頁面最底下、「Cover image」藏在可收合的右側欄，客戶只看到標題和一片空白，於是把封面圖貼進內文區。現在欄位直接在標題下方、封面圖在右欄，一眼可見。
- Showcase 編輯畫面頂端加一行提示：封面圖請用右欄的 Cover image。
- **上傳大圖失敗（「無法從伺服器取得有效的回應」）**：主機 PHP 每次請求只有 30 秒，大圖在產生縮圖時超時。新增 `inc/media.php`：上傳期間放寬時間上限，並停止產生主題用不到的 1536px／2048px 兩個尺寸（最耗時的兩張）。
- README 新增圖片／影片尺寸建議（JPG ≤ 2000px ≤ 2 MB；影片 ≤ 30 MB），以及上傳失敗時要抄下左下角的錯誤訊息。
- **新增字體大小調整**（客戶要求）：外觀 → 自訂 → Guru site settings → **Typography**，四個百分比：內文、區塊標題、頁面大標題、導覽列連結（70–150%，預設 100%）。100% 時與原設計完全相同；調整時保留所有響應式斷點。實作方式是由 `tools/build-typography.py` 從主題 CSS 自動產生 `assets/css/type/*.css`，用 CSS 變數乘上原本的字級，不修改原有樣式。

## 1.2.0 — 2026-09-11

在客戶測試站（`/staging/`）實測後修正的一批問題。

- **客戶 logo 顏色錯誤**：主機的 ImageMagick 6.8.9 重新編碼 WebP 會破壞色彩，自動產生的縮圖全毀。客戶牆與首頁跑馬燈改用原始檔，匯入 logo 時不再產生縮圖。
- **導覽列顯示舊站選單**：主題選單改名 `Guru Primary` 並以 ID 追蹤；導覽列只在選單位置指向它時才使用 WordPress 選單，否則用內建的五個連結。
- **舊頁面佔住新網址**：匯入步驟 2 會把佔用 `/about/`、`/investor/` 等網址的舊頁改為草稿（加 `-old` 後綴），讓新頁接手；匯入的頁面身分改用 `_guru_seed_key` 判斷，不再只比對標題。
- **舊網址 301**：新增 `inc/redirects.php`，`about-us`、`our-work`、`contact-us`、`service/*`、`portfolio-item/*`、`last-announcement`、`financial-information` 等自動轉到新頁面。
- **一般頁面沒有版型**：新增 `page.php`，並清除舊頁殘留的 `[vc_row]` 等短碼。
- 首頁跑馬燈預設顯示原設計的六個品牌字標；Client logo 可勾選加入跑馬燈。
- 頁尾「Follow Us」在未填社群連結時整欄隱藏。
- 匯入步驟 2 一律重設首頁、網誌頁與主選單位置。
- **Work 頁沒有影片的作品**：點擊後改為開啟該作品自己的頁面（原本會彈出空白燈箱）。
- **Work 頁瀏覽器分頁標題**改為「Work」（原本顯示後台用的「Showcase」）。
- **Blog 分類篩選**：選到沒有文章的分類時顯示「No articles in this category yet.」（原本整片空白）。
- 同一頁有多個表單時不再產生重複的 HTML id。
- 新增主題預覽圖 `screenshot.png`。

## 1.1.0 — 2026-09-10

與靜態版（Vercel）完全對齊，並修正多項功能問題。

- 首頁 About 統計區、首頁「Our Work」四格可指定（Showcase 的 Homepage position）。
- Solutions 各面板的手機截圖／影片輪播／單支影片／重點卡，可在後台編輯。
- About 頁世界地圖影片；Blog 分類篩選；Work 頁卡片與燈箱改回靜態版做法。
- Work／Blog 精簡頁尾、Solutions 無表單頁尾、Investor 頁獨立頁尾。
- Customizer 補齊所有設定（Homepage、About page、Blog、Investor page）。
- 表單來信改存獨立資料表 `{prefix}guru_enquiries`，可匯出 CSV；測試站推送正式站時排除該表即可保留來信。
- 匯入步驟 4 配合主機 30 秒執行上限可分次執行。
- 新增 `tools/`：本機 WordPress 測試環境、靜態站對照檢查、表單測試。

## 1.0.0 — 2026-08-24

第一版：全部頁面模板、四個自訂文章類型、一鍵匯入、客戶安裝說明。
