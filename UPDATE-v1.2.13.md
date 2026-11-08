# Guru 主題 v1.2.13 更新清單

**版本**：1.2.13　**日期**：2026-11-08
**安裝檔**：`guru-theme-v1.2.13-20261108.zip`／`guru-theme-v1.2.13-20261108-slim.zip`（不含影片）

測試站原本是 1.2.12。這一版把 Investor 頁接上真正的投資人公告（客戶：「investor 新增 announcement 可以參考他們 guru 原本網站」）。

---

## 一、改了什麼

| 位置 | 內容 |
|---|---|
| Investor 頁 | 分成 **Financial Information**、**Last Announcement** 兩區，各顯示最新 6 筆，下方「View All …」看全部；HKEXnews 連結、Corporate Governance、聯絡資料保留 |
| 完整列表 | 點「View All」在同一頁顯示該分類全部文件，每頁 20 筆、可翻頁，上方可返回 |
| 中英文 | 同一份文件的中英文合併成一列（英文標題，下面一行中文標題），右邊 EN／中文兩個 PDF 按鈕 |
| 舊網址 | 原網站的 /last-announcement/、/financial-information/ 會自動轉到對應的完整列表 |

資料來源就是後台的 **Investor Doc**（原網站一直在用的那一批，約 1,045 筆），所以不需要重新上傳任何文件。

## 二、客戶之後怎麼新增公告

和原本一樣：後台 **Investor Doc → 新增**，填標題、Date of publish、上傳 PDF（Document），右側勾分類（Last Announcement 或 Financial Information），發佈。中英文各新增一筆、同一天發佈，網站會自動配成一列。新增後 Investor 頁立刻更新。

## 三、上線前必須注意

測試站的投資人文件是 **2026-09-08** 從正式站複製來的。正式站之後新增的公告（例如 9/10、9/11、9/28、9/29、10/6 那幾份）測試站沒有。上線時若用測試站覆蓋正式站，要先把這段期間的新公告補進測試站（或改用只推主題、不推資料庫的方式），否則會遺失。

## 四、安裝步驟（測試站，已由 Claude 完成）

外觀 → 佈景主題 → 上傳 `guru-theme-v1.2.13-20261108.zip` → 以上傳的版本取代。不需要重跑匯入。

## 五、上傳後請確認

- [ ] Investor 頁：Financial Information、Last Announcement 各 6 筆，日期由新到舊，中英文在同一列
- [ ] 點 EN／中文 會開啟對應 PDF
- [ ] 點「View All Last Announcement」：每頁 20 筆、下方可翻頁、上方可返回
- [ ] 舊網址 https://（網站）/last-announcement/ 會轉到完整列表（正式站上線後才適用；測試站用 ?page_id 網址）
