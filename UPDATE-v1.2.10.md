# Guru 主題 v1.2.10 更新清單

**版本**：1.2.10　**日期**：2026-11-06
**安裝檔**：`guru-theme-v1.2.10-20261106.zip`／`guru-theme-v1.2.10-20261106-slim.zip`（不含影片）

測試站原本是 1.2.9。這一版處理客戶回覆要刪除的三處。

---

## 一、改了什麼

| 頁面 | 客戶說 | 處理 |
|---|---|---|
| Investor | Useful Links & Listing Information 可以刪 | 右側兩張卡片拿掉，只留「IR & Office Contacts」。後台「外觀 → 自訂 → Investor page」只給這兩張卡用的 5 個欄位也一起拿掉 |
| Blog | Insights 旁邊的一段字可以刪 | 介紹文字拿掉；後台「外觀 → 自訂」的「Blog」區塊（只有這段文字）也拿掉 |
| Work | Work 旁邊的 17 projects 可以刪 | 作品數量拿掉 |

保留不動：Investor 頁的文件清單、「View All Announcements」連結、Corporate Governance、聯絡資料、頁尾的股票代號；Blog 的「Insights」標題與分類篩選；Work 的分類篩選與作品卡。

## 二、安裝步驟（測試站，已由 Claude 完成）

外觀 → 佈景主題 → 上傳 `guru-theme-v1.2.10-20261106.zip` → 以上傳的版本取代。不需要重跑匯入。

## 三、上傳後請確認

- [ ] Investor：右側只剩「IR & Office Contacts」，沒有 Listing Information、Useful Links
- [ ] Blog：「Insights」右邊沒有文字
- [ ] Work：「Work」右邊沒有「17 Projects」，點分類篩選仍正常
- [ ] 後台 外觀 → 自訂 → Guru site settings：沒有「Blog」區塊；Investor page 沒有 Exchange、Board lot、Fiscal year end、Stock quote link、Previous IR website link
