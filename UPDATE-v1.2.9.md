# Guru 主題 v1.2.9 更新清單

**版本**：1.2.9　**日期**：2026-11-06
**安裝檔**：`guru-theme-v1.2.9-20261106.zip`／`guru-theme-v1.2.9-20261106-slim.zip`（不含影片）

測試站原本是 1.2.8。這一版只處理客戶 11/06 清單的第 5 項：Investor 頁變亂碼。

---

## 一、改了什麼

| 客戶說 | 原因 | 處理 |
|---|---|---|
| Investor 那邊變了一堆亂碼 | 不是編碼壞掉，是舊網站用 Visual Composer 排版留下的版面代碼（`[vc_row …]` 這類）直接顯示成文字。導覽列的 Investor 連到的是**舊站的 Investor 頁**，它的內容全是這種代碼；我們新建的「Investor Relations」頁網址被擠成 `investor-2`，沒被用到 | 1. 主題：Investor 模板也會先清掉舊代碼（和一般頁面一樣）<br>2. 測試站資料：舊 Investor 頁改成**草稿**、網址改成 `investor-old`；新的「Investor Relations」頁接手 `investor`，導覽列自動連到它 |

舊頁沒有刪除，只是轉成草稿，需要時可以在「頁面」裡找回來。

## 二、安裝步驟（測試站，已由 Claude 完成）

1. 外觀 → 佈景主題 → 上傳 `guru-theme-v1.2.9-20261106.zip` → 以上傳的版本取代
2. 頁面：舊「Investor」（ID 14094）→ 草稿、網址 `investor-old`；「Investor Relations」（ID 24394）→ 網址 `investor`

## 三、上傳後請確認

- [ ] 導覽列點 Investor：頁面上沒有任何 `[vc_…]`、`[eltd_…]` 這類文字
- [ ] Investor 頁正常顯示 Investor Relations 標題、Financial Results & Reports、Corporate Governance、Listing Information、聯絡資料
- [ ] 後台「頁面」：「Investor」是草稿，「Investor Relations」是已發佈

## 四、還沒處理（提醒）

- Investor 頁上「Financial Results & Reports」仍是示範資料（在 外觀 → 自訂 → Investor page 修改）。正式站的 1,045 筆投資人文件還沒接上，這是上線前要做的另一項工作。
