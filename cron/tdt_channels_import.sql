-- ============================================================
-- Importación inicial de canales TDTChannels (lists/tv.json)
-- Generado 2026-06-25 19:14:15 — formato m3u8 únicamente.
-- A partir de aquí, cron/tdt_channels_sync.php mantiene url/epg al día.
-- ============================================================

ALTER TABLE fuentes ADD COLUMN IF NOT EXISTS tdt_ref VARCHAR(180) DEFAULT NULL;
ALTER TABLE fuentes ADD UNIQUE INDEX IF NOT EXISTS idx_fuentes_tdt_ref (tdt_ref);

START TRANSACTION;

-- ── +24 [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('+24', 'https://pbs.twimg.com/profile_images/1634293543987453954/mb1Rzmso_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('+24 1', @canal_id, 'https://ztnr.rtve.es/ztnr/6108696.m3u8', 441, 1, NULL, 1, 1, '+24#0'),
('+24 2', @canal_id, 'https://ztnr.rtve.es/ztnr/6108703.m3u8', 441, 1, NULL, 1, 1, '+24#1'),
('+24 3', @canal_id, 'https://ztnr.rtve.es/ztnr/6108704.m3u8', 441, 1, NULL, 1, 1, '+24#2'),
('+24 4', @canal_id, 'https://ztnr.rtve.es/ztnr/6108706.m3u8', 441, 1, NULL, 1, 1, '+24#3'),
('+24 5', @canal_id, 'https://ztnr.rtve.es/ztnr/6108720.m3u8', 441, 1, NULL, 1, 1, '+24#4'),
('+24 6', @canal_id, 'https://ztnr.rtve.es/ztnr/6108721.m3u8', 441, 1, NULL, 1, 1, '+24#5');

-- ── +tdp [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('+tdp', 'https://graph.facebook.com/teledeporteRTVE/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('+tdp 1', @canal_id, 'https://ztnr.rtve.es/ztnr/6712432.m3u8', 441, 1, NULL, 1, 1, '+tdp#0'),
('+tdp 2', @canal_id, 'https://ztnr.rtve.es/ztnr/6712453.m3u8', 441, 1, NULL, 1, 1, '+tdp#1'),
('+tdp 3', @canal_id, 'https://ztnr.rtve.es/ztnr/6712402.m3u8', 441, 1, NULL, 1, 1, '+tdp#2'),
('+tdp 4', @canal_id, 'https://ztnr.rtve.es/ztnr/6712407.m3u8', 441, 1, NULL, 1, 1, '+tdp#3'),
('+tdp 5', @canal_id, 'https://ztnr.rtve.es/ztnr/6712431.m3u8', 441, 1, NULL, 1, 1, '+tdp#4'),
('+tdp 6', @canal_id, 'https://ztnr.rtve.es/ztnr/6712410.m3u8', 441, 1, NULL, 1, 1, '+tdp#5'),
('+tdp 7', @canal_id, 'https://ztnr.rtve.es/ztnr/6712426.m3u8', 441, 1, NULL, 1, 1, '+tdp#6'),
('+tdp 8', @canal_id, 'https://ztnr.rtve.es/ztnr/6712411.m3u8', 441, 1, NULL, 1, 1, '+tdp#7'),
('+tdp 9', @canal_id, 'https://ztnr.rtve.es/ztnr/6712456.m3u8', 441, 1, NULL, 1, 1, '+tdp#8');

-- ── 1Muz Bielorrusia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('1Muz Bielorrusia', 'https://graph.facebook.com/onemusicchannel/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('1Muz Bielorrusia 1', @canal_id, 'http://hz1.teleport.cc/HLS/HD.m3u8', NULL, 1, NULL, 1, 1, '1Muz Bielorrusia#0'),
('1Muz Bielorrusia 2', @canal_id, 'http://hz1.teleport.cc/HLS/SD.m3u8', NULL, 1, NULL, 1, 1, '1Muz Bielorrusia#1');

-- ── 3ABN USA [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3ABN USA', 'https://graph.facebook.com/3abn.org/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3ABN USA 1', @canal_id, 'https://3abn.bozztv.com/3abn2/Lat_live/smil:Lat_live.smil/playlist.m3u8', 542, 1, NULL, 1, 1, '3ABN USA#0'),
('3ABN USA 2', @canal_id, 'https://3abn.bozztv.com/3abn2/3abn_live/smil:3abn_live.smil/playlist.m3u8', 542, 1, NULL, 1, 1, '3ABN USA#1');

-- ── 3Cat Anime [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Anime', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Anime', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/tem2-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Anime#0');

-- ── 3Cat Càmeres del temps [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Càmeres del temps', 'https://graph.facebook.com/3CatInfoElTemps/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Càmeres del temps', @canal_id, 'https://directes-tv-int.3catdirectes.cat/live-content/beauties-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Càmeres del temps#0');

-- ── 3Cat Doraemon [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Doraemon', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Doraemon', @canal_id, 'https://fast-tailor.3catdirectes.cat/v1/channel/doraemon/hls.m3u8', 441, 1, NULL, 1, 1, '3Cat Doraemon#0');

-- ── 3Cat El búnquer [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat El búnquer', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat El búnquer', @canal_id, 'https://fast-tailor.3catdirectes.cat/v1/channel/bunquer/hls.m3u8', 441, 1, NULL, 1, 1, '3Cat El búnquer#0');

-- ── 3Cat Exclusiu 1 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Exclusiu 1', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Exclusiu 1 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-content/oca1-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 1#0'),
('3Cat Exclusiu 1 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/oca1-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 1#1');

-- ── 3Cat Exclusiu 2 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Exclusiu 2', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Exclusiu 2 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-content/oca2-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 2#0'),
('3Cat Exclusiu 2 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/oca2-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 2#1');

-- ── 3Cat Exclusiu 3 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Exclusiu 3', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Exclusiu 3 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-content/oca3-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 3#0'),
('3Cat Exclusiu 3 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/oca3-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Exclusiu 3#1');

-- ── 3Cat Info [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Info', 'https://pbs.twimg.com/profile_images/1968163923477098496/blka6O_j_200x200.jpg', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Info', @canal_id, 'https://directes-tv-int.3catdirectes.cat/live-content/canal324-hls/master.m3u8', 441, 1, '324.TV', 1, 1, '3Cat Info#0');

-- ── 3Cat Joc de Cartes [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Joc de Cartes', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Joc de Cartes', @canal_id, 'https://fast-tailor.3catdirectes.cat/v1/channel/joc-de-cartes/hls.m3u8', 441, 1, NULL, 1, 1, '3Cat Joc de Cartes#0');

-- ── 3Cat Plats bruts [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Plats bruts', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Plats bruts', @canal_id, 'https://fast-tailor.3catdirectes.cat/v1/channel/ccma-channel2/hls.m3u8', 441, 1, NULL, 1, 1, '3Cat Plats bruts#0');

-- ── 3Cat Verdi Clàssics [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Verdi Clàssics', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Verdi Clàssics', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/tem1-hls/master.m3u8', 441, 1, NULL, 1, 1, '3Cat Verdi Clàssics#0');

-- ── 3Cat Vinagreta [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('3Cat Vinagreta', 'https://graph.facebook.com/som3Cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('3Cat Vinagreta', @canal_id, 'https://fast-tailor.3catdirectes.cat/v1/channel/vinagreta/hls.m3u8', 441, 1, NULL, 1, 1, '3Cat Vinagreta#0');

-- ── 4FUN TV Polonia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('4FUN TV Polonia', 'https://graph.facebook.com/4FUN.TV/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('4FUN TV Polonia', @canal_id, 'https://stream.4fun.tv:8888/hls/4f_high/index.m3u8', NULL, 1, NULL, 1, 1, '4FUN TV Polonia#0');

-- ── 7 TeleValencia [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7 TeleValencia', 'https://pbs.twimg.com/profile_images/1876660632478437376/rbEqYeqC_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7 TeleValencia', @canal_id, 'https://play.cdn.enetres.net/9E9557EFCEBB43A89CEC8FBD3C500247022/028/playlist.m3u8', 441, 1, '7Televalencia.TV', 1, 1, '7 TeleValencia#0');

-- ── 7TV Aljarafe [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Aljarafe', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Aljarafe', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVALJARAFE.m3u8', 441, 1, NULL, 1, 1, '7TV Aljarafe#0');

-- ── 7TV Almería [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Almería', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Almería', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVALMERIA.m3u8', 441, 1, NULL, 1, 1, '7TV Almería#0');

-- ── 7TV Andalucía [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Andalucía', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Andalucía', @canal_id, 'https://especial7tv.gestec-video.com/hls/regional.m3u8', 441, 1, '7TV_Andalucía.TV', 1, 1, '7TV Andalucía#0');

-- ── 7TV Arcos [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Arcos', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Arcos', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVARCOS.m3u8', 441, 1, NULL, 1, 1, '7TV Arcos#0');

-- ── 7TV Campo de Gibraltar [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Campo de Gibraltar', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Campo de Gibraltar', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVALGECIRAS.m3u8', 441, 1, NULL, 1, 1, '7TV Campo de Gibraltar#0');

-- ── 7TV Cádiz [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Cádiz', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Cádiz', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVCADIZ.m3u8', 441, 1, NULL, 1, 1, '7TV Cádiz#0');

-- ── 7TV Córdoba [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Córdoba', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Córdoba', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVCORDOBA.m3u8', 441, 1, NULL, 1, 1, '7TV Córdoba#0');

-- ── 7TV Granada [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Granada', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Granada', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVGRANADA.m3u8', 441, 1, NULL, 1, 1, '7TV Granada#0');

-- ── 7TV Huelva [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Huelva', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Huelva', @canal_id, 'https://streaming004.gestec-video.com/hls/regional.m3u8', 441, 1, NULL, 1, 1, '7TV Huelva#0');

-- ── 7TV Jaén [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Jaén', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Jaén', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVJAEN.m3u8', 441, 1, NULL, 1, 1, '7TV Jaén#0');

-- ── 7TV Jerez [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Jerez', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Jerez', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVJEREZ.m3u8', 441, 1, NULL, 1, 1, '7TV Jerez#0');

-- ── 7TV Linares [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Linares', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Linares', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVLINARES.m3u8', 441, 1, NULL, 1, 1, '7TV Linares#0');

-- ── 7TV Málaga [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Málaga', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&0height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Málaga', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVMALAGA.m3u8', 441, 1, NULL, 1, 1, '7TV Málaga#0');

-- ── 7TV Rota [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Rota', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Rota', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVROTA.m3u8', 441, 1, NULL, 1, 1, '7TV Rota#0');

-- ── 7TV San Fernando [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV San Fernando', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV San Fernando', @canal_id, 'https://streaming004.gestec-video.com/hls/7TVSANFERNANDO.m3u8', 441, 1, NULL, 1, 1, '7TV San Fernando#0');

-- ── 7TV Sevilla [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('7TV Sevilla', 'https://graph.facebook.com/7TelevisionAndalucia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('7TV Sevilla', @canal_id, 'https://especial7tv.gestec-video.com/hls/7TVSEVILLA.m3u8', 441, 1, NULL, 1, 1, '7TV Sevilla#0');

-- ── 8 La Marina TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('8 La Marina TV', 'https://graph.facebook.com/8lamarinatelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('8 La Marina TV', @canal_id, 'https://streaming005.gestec-video.com/hls/canal24.m3u8', 441, 1, NULL, 1, 1, '8 La Marina TV#0');

-- ── 8TV Chiclana [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('8TV Chiclana', 'https://graph.facebook.com/8tvChiclana/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('8TV Chiclana', @canal_id, 'https://s.emisoras.tv:8081/chiclana/index.m3u8', 441, 1, NULL, 1, 1, '8TV Chiclana#0');

-- ── 8TV Sierra de Cádiz [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('8TV Sierra de Cádiz', 'https://graph.facebook.com/8tvChiclana/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('8TV Sierra de Cádiz', @canal_id, 'https://s.emisoras.tv:8081/sierradecadiz/index.m3u8', 441, 1, NULL, 1, 1, '8TV Sierra de Cádiz#0');

-- ── 9 la Loma TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('9 la Loma TV', 'https://graph.facebook.com/9laloma/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('9 la Loma TV', @canal_id, 'https://9laloma.tv/live.m3u8', 441, 1, '9LaLoma.TV', 1, 1, '9 la Loma TV#0');

-- ── 12TV Alicante [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('12TV Alicante', 'https://graph.facebook.com/12tv.es/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('12TV Alicante', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16126&live=1&avod=0&hls_marker=1&position=preroll&pod_duration=120&app_bundle=com.streamingconnect.viva&ssai_enabled=1&content_channel=12tv&app_domain=mirametv.live&app_category=linear_tv&content_cat=IAB1&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&min_ad_duration=6&max_ad_duration=120&content_id=mirametv_live', 441, 1, NULL, 1, 1, '12TV Alicante#0');

-- ── 24h [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('24h', 'https://pbs.twimg.com/profile_images/1634293543987453954/mb1Rzmso_200x200.jpg', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('24h 1', @canal_id, 'https://ztnr.rtve.es/ztnr/1694255.m3u8', 441, 1, '24Horas.TV', 1, 1, '24h#0'),
('24h 2', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/24h/24h_main_dvr.m3u8', 441, 1, '24Horas.TV', 1, 1, '24h#1'),
('24h 3', @canal_id, 'https://dpcj1q84r586o.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-zkqd2yaveiqbt/24HES.m3u8', 441, 1, '24Horas.TV', 1, 1, '24h#2');

-- ── 24h Canarias [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('24h Canarias', 'https://pbs.twimg.com/profile_images/1634293543987453954/mb1Rzmso_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('24h Canarias', @canal_id, 'https://ztnr.rtve.es/ztnr/5473142.m3u8', 441, 1, '24H_CAN.TV', 1, 1, '24h Canarias#0');

-- ── 24h Catalunya [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('24h Catalunya', 'https://pbs.twimg.com/profile_images/1634293543987453954/mb1Rzmso_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('24h Catalunya', @canal_id, 'https://ztnr.rtve.es/ztnr/4952053.m3u8', 441, 1, '24H_CAT', 1, 1, '24h Catalunya#0');

-- ── 28 Kanala [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('28 Kanala', 'https://graph.facebook.com/28kanala/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('28 Kanala', @canal_id, 'https://streaming.28kanala.eus/hls/z.m3u8', 441, 1, NULL, 1, 1, '28 Kanala#0');

-- ── 30A Music USA [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('30A Music USA', 'https://graph.facebook.com/30atv/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('30A Music USA', @canal_id, 'https://30a-tv.com/feeds/ceftech/30atvmusic.m3u8', 542, 1, NULL, 1, 1, '30A Music USA#0');

-- ── 33 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('33', 'https://graph.facebook.com/Canal33/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('33 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-origin/c33-super3-hls/master.m3u8', 441, 1, 'C33.TV', 1, 1, '33#0'),
('33 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-origin/c33-super3-hls/master.m3u8', 441, 1, 'C33.TV', 1, 1, '33#1');

-- ── 33TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('33TV', 'https://graph.facebook.com/33Television/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('33TV', @canal_id, 'https://limited43.todostreaming.es/live/simbana-livestream.m3u8', 441, 1, NULL, 1, 1, '33TV#0');

-- ── 70-80's Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('70-80\'s Italia', 'https://play-lh.googleusercontent.com/OwKy6Ef_hOsjuSYtgh5aOHndFs2uo9evgrrjO4DYiOwXiAtWtSZiFMWY_OIcLU170NA=w200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('70-80\'s Italia', @canal_id, 'https://585b674743bbb.streamlock.net/9050/9050/playlist.m3u8', 408, 1, NULL, 1, 1, '70-80\'s Italia#0');

-- ── 101TV Almería [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Almería', 'https://graph.facebook.com/101Almeria/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Almería', @canal_id, 'https://liveingesta318.cdnmedia.tv/101weblive/smil:ALMERIA.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Almería#0');

-- ── 101TV Antequera [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Antequera', 'https://graph.facebook.com/101tvAntequera/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Antequera', @canal_id, 'https://liveingesta318.cdnmedia.tv/101weblive/smil:antequera.smil/playlist.m3u8', 441, 1, '101Antequera.TV', 1, 1, '101TV Antequera#0');

-- ── 101TV Costa del Sol [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Costa del Sol', 'https://yt3.googleusercontent.com/dFDfeITvIVgSQQSNlgLw1WJ98Icuw-WlYJXNp31UhoABX3LOrJjXafo6a2H9EfhrA9Ah9e84=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Costa del Sol', @canal_id, 'https://liveingesta318.cdnmedia.tv/101weblive/smil:BENALMADENA.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Costa del Sol#0');

-- ── 101TV Cádiz [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Cádiz', 'https://yt3.googleusercontent.com/dFDfeITvIVgSQQSNlgLw1WJ98Icuw-WlYJXNp31UhoABX3LOrJjXafo6a2H9EfhrA9Ah9e84=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Cádiz', @canal_id, 'https://liveingesta318.cdnmedia.tv/101weblive/smil:CADIZ.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Cádiz#0');

-- ── 101TV Granada [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Granada', 'https://graph.facebook.com/101tvgranada/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Granada', @canal_id, 'https://liveingesta318.cdnmedia.tv/101weblive/smil:granada.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Granada#0');

-- ── 101TV Málaga [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Málaga', 'https://graph.facebook.com/101tvmalaga/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Málaga', @canal_id, 'https://liveingesta318.cdnmedia.tv/101televisionlive/smil:malagaott.smil/playlist.m3u8?DVR', 441, 1, '101Malaga.TV', 1, 1, '101TV Málaga#0');

-- ── 101TV Málaga Cofrade [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Málaga Cofrade', 'https://graph.facebook.com/101tvmalaga/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Málaga Cofrade', @canal_id, 'https://liveingesta318.cdnmedia.tv/101televisionlive/smil:cofradeott1.smil/playlist.m3u8', 441, 1, '101Malaga.TV', 1, 1, '101TV Málaga Cofrade#0');

-- ── 101TV Sevilla [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Sevilla', 'https://graph.facebook.com/101TVSevilla/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Sevilla', @canal_id, 'https://liveingesta318.cdnmedia.tv/101televisionlive/smil:sevillaott.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Sevilla#0');

-- ── 101TV Sevilla Cofrade [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('101TV Sevilla Cofrade', 'https://graph.facebook.com/101TVSevilla/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('101TV Sevilla Cofrade', @canal_id, 'https://liveingesta318.cdnmedia.tv/101televisionlive/smil:sevillacofrade2.smil/playlist.m3u8', 441, 1, NULL, 1, 1, '101TV Sevilla Cofrade#0');

-- ── ABC News Australia [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ABC News Australia', 'https://graph.facebook.com/abcnews.au/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ABC News Australia', @canal_id, 'https://abc-news-dmd-streams-1.akamaized.net/out/v1/abc83881886746b0802dc3e7ca2bc792/index.m3u8', NULL, 1, NULL, 1, 1, 'ABC News Australia#0');

-- ── ABC TV Paraguay [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ABC TV Paraguay', 'https://graph.facebook.com/ABCTVpy/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ABC TV Paraguay', @canal_id, 'https://cdn.jwplayer.com/live/broadcast/aide2636.m3u8', NULL, 1, NULL, 1, 1, 'ABC TV Paraguay#0');

-- ── Activa TV España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Activa TV España', 'https://graph.facebook.com/activafm.radiomusical/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Activa TV España', @canal_id, 'https://streamtv.mediasector.es/hls/activatv/index.m3u8', NULL, 1, NULL, 1, 1, 'Activa TV España#0');

-- ── ADN Noticias Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ADN Noticias Mexico', 'https://pbs.twimg.com/profile_images/1968512728059850752/KUWD445m_400x400.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ADN Noticias Mexico', @canal_id, 'https://mdstrm.com/live-stream-playlist/60b578b060947317de7b57ac.m3u8', 936, 1, 'ADN40.TV', 1, 1, 'ADN Noticias Mexico#0');

-- ── Afortunadas TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Afortunadas TV', 'https://graph.facebook.com/afortunadastv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Afortunadas TV', @canal_id, 'https://cloudvideo.servers10.com:8081/8108/index.m3u8', 441, 1, NULL, 1, 1, 'Afortunadas TV#0');

-- ── Agrotendencia [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Agrotendencia', 'https://graph.facebook.com/agrotendencia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Agrotendencia', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16427&hls_marker=1&position=preroll&pod_duration=120&app_bundle=com.streamingconnect.viva&ssai_enabled=1&content_channel=mirametv&app_domain=mirametv.live&app_category=linear_tv&content_cat=IAB1&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&gdpr=[GDPR]&content_id=mirametv_live&device_type=[DEVICE_TYPE]&ip=[IP]&min_ad_duration=6&max_ad_duration=120&ua=[UA]', NULL, 1, NULL, 1, 1, 'Agrotendencia#0');

-- ── AKC TV Dogs [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('AKC TV Dogs', 'https://graph.facebook.com/WatchAKCTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('AKC TV Dogs 1', @canal_id, 'https://install.akctvcontrol.com/speed/broadcast/138/desktop-playlist.m3u8', NULL, 1, NULL, 1, 1, 'AKC TV Dogs#0'),
('AKC TV Dogs 2', @canal_id, 'https://install.akctvcontrol.com/speed/broadcast/141/desktop-playlist.m3u8', NULL, 1, NULL, 1, 1, 'AKC TV Dogs#1');

-- ── Alacantí TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Alacantí TV', 'https://graph.facebook.com/alacantitv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Alacantí TV', @canal_id, 'https://streaming01.gestec-video.com/hls/artequatreAlacanti.m3u8', 441, 1, 'Alacanti.TV', 1, 1, 'Alacantí TV#0');

-- ── Al Arabiya Emiratos Árabes [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Al Arabiya Emiratos Árabes', 'https://graph.facebook.com/AlArabiya/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Al Arabiya Emiratos Árabes', @canal_id, 'https://live.alarabiya.net/alarabiapublish/alarabiya.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Al Arabiya Emiratos Árabes#0');

-- ── Alcarria TV [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Alcarria TV', 'https://graph.facebook.com/AlcarriaTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Alcarria TV 1', @canal_id, 'https://cls.alcarria.tv/live/alcarriatv-livestream.m3u8', 441, 1, NULL, 1, 1, 'Alcarria TV#0'),
('Alcarria TV 2', @canal_id, 'http://217.182.77.27/live/alcarriatv-livestream.m3u8', 441, 1, NULL, 1, 1, 'Alcarria TV#1');

-- ── Al cielo con ella (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Al cielo con ella (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Al cielo con ella (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/16862827.m3u8', 441, 1, 'RTVE_PopUp.TV', 1, 1, 'Al cielo con ella (RTVE)#0');

-- ── Al Jazeera Catar [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Al Jazeera Catar', 'https://graph.facebook.com/aljazeera/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Al Jazeera Catar 1', @canal_id, 'https://live-hls-web-aje-gcp.thehlive.com/AJE/index.m3u8', NULL, 1, 'AlJQ.TV', 1, 1, 'Al Jazeera Catar#0'),
('Al Jazeera Catar 2', @canal_id, 'https://live-hls-web-aja.getaj.net/AJA/index.m3u8', NULL, 1, 'AlJQ.TV', 1, 1, 'Al Jazeera Catar#1');

-- ── Almería 24h TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Almería 24h TV', 'https://graph.facebook.com/107654981928274/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Almería 24h TV', @canal_id, 'https://broadcast1.radioponiente.org/almeria24h_aac/index.m3u8', 441, 1, 'Almería_24h.TV', 1, 1, 'Almería 24h TV#0');

-- ── Almería TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Almería TV', 'https://graph.facebook.com/AlmerTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Almería TV', @canal_id, 'https://broadcast1.radioponiente.org/atv/index.m3u8', 441, 1, NULL, 1, 1, 'Almería TV#0');

-- ── America's Voice USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('America\'s Voice USA', 'https://graph.facebook.com/RealAmericasVoice/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('America\'s Voice USA', @canal_id, 'https://d2jiqiw4g5lj5k.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/AmericasVoiceChannel-prod/AVSamsung/AVSamsung.m3u8', 542, 1, NULL, 1, 1, 'America\'s Voice USA#0');

-- ── America TeVe USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('America TeVe USA', 'https://graph.facebook.com/americateve/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('America TeVe USA', @canal_id, 'https://live.gideo.video/americateve2/master.m3u8', 542, 1, NULL, 1, 1, 'America TeVe USA#0');

-- ── Andalucía Cocina [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Andalucía Cocina', 'https://yt3.googleusercontent.com/ytc/APkrFKb6DZpbxOMbN_VANCdenLck4ceg7gxMk5tnkjmM=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Andalucía Cocina', @canal_id, 'https://cloud.fastchannel.es/mic/manifiest/hls/acocina/acocina.m3u8', 441, 1, NULL, 1, 1, 'Andalucía Cocina#0');

-- ── Andalucía Turismo [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Andalucía Turismo', 'https://yt3.googleusercontent.com/ytc/APkrFKYsl5e6jEIMtXIoTUHvkJqXxDfASrvQP_QFRRww=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Andalucía Turismo', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?&network_id=12685&live=1', 441, 1, NULL, 1, 1, 'Andalucía Turismo#0');

-- ── Anove TV [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Anove TV', 'https://graph.facebook.com/anove.tv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Anove TV', @canal_id, 'https://cloud.streamingconnect.tv/hls/anove/anove.m3u8', 441, 1, NULL, 1, 1, 'Anove TV#0');

-- ── Arabí TV [Spain / R. de Murcia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Arabí TV', 'https://graph.facebook.com/arabitvyecla/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Arabí TV', @canal_id, 'https://streamtv2.elitecomunicacion.cloud:3628/live/arabitv2025live.m3u8', 441, 1, NULL, 1, 1, 'Arabí TV#0');

-- ── Aragón Deporte [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Aragón Deporte', 'https://graph.facebook.com/aragondeporte/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Aragón Deporte 1', @canal_id, 'https://cartv-streaming.aranova.es/hls/live/adeportes_deporte7.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#0'),
('Aragón Deporte 2', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/adeportes_deporte6.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#1'),
('Aragón Deporte 3', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/adeportes_deporte5.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#2'),
('Aragón Deporte 4', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/adeportes_deporte4.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#3'),
('Aragón Deporte 5', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/adeportes_deporte3.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#4'),
('Aragón Deporte 6', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/adeportes_deporte2.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#5'),
('Aragón Deporte 7', @canal_id, 'https://cartv-streaming.aranova.es/hls/live/adeportes_deporte1.m3u8', 441, 1, 'AragonD.TV', 1, 1, 'Aragón Deporte#6');

-- ── Aragón Noticias [Spain / Aragón] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Aragón Noticias', 'https://graph.facebook.com/AragonNoticias/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Aragón Noticias', @canal_id, 'https://cartv-streaming.aranova.es/hls/live/anoticias_canal3.m3u8', 441, 1, NULL, 1, 1, 'Aragón Noticias#0');

-- ── Aragón TV [Spain / Aragón] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Aragón TV', 'https://graph.facebook.com/AragonTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Aragón TV', @canal_id, 'https://cartv.streaming.aranova.es/hls/live/aragontv_canal1.m3u8', 441, 1, 'AragonTV.TV', 1, 1, 'Aragón TV#0');

-- ── ARD Das Erste Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ARD Das Erste Alemania', 'https://pbs.twimg.com/profile_images/1605959306435756038/_EiuBjQ8_200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ARD Das Erste Alemania', @canal_id, 'https://daserste-live.ard-mcdn.de/daserste/live/hls/int/master.m3u8', 723, 1, NULL, 1, 1, 'ARD Das Erste Alemania#0');

-- ── Arirang TV Corea del Sur [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Arirang TV Corea del Sur', 'https://graph.facebook.com/arirangtv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Arirang TV Corea del Sur', @canal_id, 'https://amdlive-ch01-g-ctnd-com.akamaized.net/arirang_1gch/smil:arirang_1gch.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Arirang TV Corea del Sur#0');

-- ── Atlántico Televisión [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Atlántico Televisión', 'https://pbs.twimg.com/profile_images/1779498186727456768/bIHqyk7p_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Atlántico Televisión', @canal_id, 'https://live.atlanticotelevision.com/hls/hi/index.m3u8', 441, 1, NULL, 1, 1, 'Atlántico Televisión#0');

-- ── ATV Andorra [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ATV Andorra', 'https://graph.facebook.com/rtva.andorra/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ATV Andorra', @canal_id, 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8', NULL, 1, 'ATVHD.TV', 1, 1, 'ATV Andorra#0');

-- ── Badalona TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Badalona TV', 'https://pbs.twimg.com/profile_images/1993636082642976768/7YX0mFB8_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Badalona TV', @canal_id, 'https://liveingesta318.cdnmedia.tv/badalonatvlive/smil:live.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_Televisio_BDN.TV', 1, 1, 'Badalona TV#0');

-- ── betevé [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('betevé', 'https://graph.facebook.com/betevecat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('betevé', @canal_id, 'https://cdnapisec.kaltura.com/p/2346171/sp/234617100/playManifest/entryId/1_vfibi2fe/protocol/https/format/applehttp/a.m3u8?referrer=aHR0cHM6Ly9iZXRldmUuY2F0', 441, 1, 'BTV.TV', 1, 1, 'betevé#0');

-- ── BFM TV Francia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('BFM TV Francia', 'https://graph.facebook.com/BFMTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('BFM TV Francia', @canal_id, 'https://live-cdn-stream-euw1.bfmtv.bct.nextradiotv.com/master.m3u8', 692, 1, NULL, 1, 1, 'BFM TV Francia#0');

-- ── Bloomberg Europe [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Bloomberg Europe', 'https://pbs.twimg.com/profile_images/991792042094354432/DG1Ruupb_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Bloomberg Europe', @canal_id, 'https://www.bloomberg.com/media-manifest/streams/eu.m3u8', NULL, 1, 'Bloom.TV', 1, 1, 'Bloomberg Europe#0');

-- ── Bloomberg Quicktake [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Bloomberg Quicktake', 'https://yt3.ggpht.com/fTP5oS376nZhVbS55-OocogCJCDH4UXIyRrirF6keIya4AN4I54TmLkFnnjvE4FRq5UMv8BO=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Bloomberg Quicktake', @canal_id, 'https://www.bloomberg.com/media-manifest/streams/qt.m3u8', NULL, 1, NULL, 1, 1, 'Bloomberg Quicktake#0');

-- ── Bloomberg USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Bloomberg USA', 'https://pbs.twimg.com/profile_images/991792042094354432/DG1Ruupb_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Bloomberg USA 1', @canal_id, 'https://www.bloomberg.com/media-manifest/streams/us.m3u8', 542, 1, NULL, 1, 1, 'Bloomberg USA#0'),
('Bloomberg USA 2', @canal_id, 'https://www.bloomberg.com/media-manifest/streams/us-event.m3u8', 542, 1, NULL, 1, 1, 'Bloomberg USA#1');

-- ── Bon Dia TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Bon Dia TV', 'https://i2.wp.com/blocs.mesvilaweb.cat/wp-content/uploads/sites/1858/2018/11/BONDIA.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Bon Dia TV', @canal_id, 'https://directes-tv-int.3catdirectes.cat/live-content/bondia-hls/master.m3u8', 441, 1, 'BonDiaTV_CAT.TV', 1, 1, 'Bon Dia TV#0');

-- ── BR Bayerischer Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('BR Bayerischer Alemania', 'https://graph.facebook.com/BR24/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('BR Bayerischer Alemania', @canal_id, 'https://mcdn.br.de/br/fs/bfs_sued/hls/int/master.m3u8', 723, 1, NULL, 1, 1, 'BR Bayerischer Alemania#0');

-- ── Burriana TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Burriana TV', 'https://graph.facebook.com/burrianateve/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Burriana TV', @canal_id, 'https://stream.burrianateve.com/hls/abr_btv/index.m3u8', 441, 1, NULL, 1, 1, 'Burriana TV#0');

-- ── BUZZR TV USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('BUZZR TV USA', 'https://graph.facebook.com/BUZZRtv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('BUZZR TV USA', @canal_id, 'https://buzzrota-ono.amagi.tv/playlist.m3u8', 542, 1, NULL, 1, 1, 'BUZZR TV USA#0');

-- ── Cadena Elite España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cadena Elite España', 'https://graph.facebook.com/cadena.elitegranada/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cadena Elite España', @canal_id, 'https://cloudvideo.servers10.com:8081/8004/index.m3u8', NULL, 1, NULL, 1, 1, 'Cadena Elite España#0');

-- ── Canal 1 Mar Menor Torre Pacheco [Spain / R. de Murcia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 1 Mar Menor Torre Pacheco', 'https://graph.facebook.com/tuwebtv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 1 Mar Menor Torre Pacheco', @canal_id, 'https://directo.tuwebtv.es/canal1.m3u8', 441, 1, NULL, 1, 1, 'Canal 1 Mar Menor Torre Pacheco#0');

-- ── Canal 3 Biar [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 3 Biar', 'https://yt3.googleusercontent.com/ytc/AIdro_nQt5rluj5KRX0HGcMvHLxSUJuhjZe4Y1GfuA0NjpHf5Q=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 3 Biar', @canal_id, 'https://avantstreaming.es/hls/canal3.m3u8', 441, 1, NULL, 1, 1, 'Canal 3 Biar#0');

-- ── Canal 4 Mancha [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 4 Mancha', 'https://graph.facebook.com/canal4villarrobledo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 4 Mancha', @canal_id, 'https://5924d3ad0efcf.streamlock.net/canal4/canal4live/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal 4 Mancha#0');

-- ── Canal 4 Tenerife [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 4 Tenerife', 'https://graph.facebook.com/CANAL4TENERIFE/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 4 Tenerife', @canal_id, 'https://videoserver.tmcreativos.com:19360/wwzthqpupr/wwzthqpupr.m3u8', 441, 1, 'Canal4_Tenerife.TV', 1, 1, 'Canal 4 Tenerife#0');

-- ── Canal 4 TV Cataluña [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 4 TV Cataluña', 'https://graph.facebook.com/GRUP4COM/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 4 TV Cataluña', @canal_id, 'https://5caf24a595d94.streamlock.net:1937/8014/8014/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal 4 TV Cataluña#0');

-- ── Canal 4 TV Mallorca [Spain / Illes Balears] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 4 TV Mallorca', 'https://graph.facebook.com/GRUP4COM/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 4 TV Mallorca', @canal_id, 'https://5caf24a595d94.streamlock.net:1937/8110/8110/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal 4 TV Mallorca#0');

-- ── Canal 6 Multimedios Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 6 Multimedios Mexico', 'https://graph.facebook.com/multimediostv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 6 Multimedios Mexico', @canal_id, 'https://mdstrm.com/live-stream-playlist/57b4dbf5dbbfc8f16bb63ce1.m3u8', 936, 1, NULL, 1, 1, 'Canal 6 Multimedios Mexico#0');

-- ── Canal 10 Empordà [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 10 Empordà', 'https://graph.facebook.com/canal10emporda/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 10 Empordà', @canal_id, 'https://ventdelnord.tv:8080/escala/directe.m3u8', 441, 1, 'Xarxa_Canal_10_Emporda.TV', 1, 1, 'Canal 10 Empordà#0');

-- ── Canal 33 Madrid [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 33 Madrid', 'https://graph.facebook.com/Canal33Madrid/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 33 Madrid', @canal_id, 'https://media2.streambrothers.com:1936/8140/8140/.m3u8', 441, 1, 'C33M.TV', 1, 1, 'Canal 33 Madrid#0');

-- ── Canal 45 TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 45 TV', 'https://graph.facebook.com/canal45television/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 45 TV', @canal_id, 'https://nlb1-live.emitstream.com/hls/625csn5et2iszm9oze65/master.m3u8', 441, 1, NULL, 1, 1, 'Canal 45 TV#0');

-- ── Canal 56 [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 56', 'https://graph.facebook.com/canal56televisio/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 56', @canal_id, 'https://videos.canal56.com/directe/stream/index.m3u8', 441, 1, NULL, 1, 1, 'Canal 56#0');

-- ── Canal 2000 [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal 2000', 'https://graph.facebook.com/canal2000/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal 2000', @canal_id, 'http://canal2000.berkano-systems.net/streaming/streams/canal2000-720p.m3u8', 441, 1, NULL, 1, 1, 'Canal 2000#0');

-- ── CanalCosta [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CanalCosta', 'https://graph.facebook.com/canalcosta/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CanalCosta', @canal_id, 'https://5f71743aa95e4.streamlock.net:1936/CanalcostaTV/endirecto/playlist.m3u8', 441, 1, 'CanalCosta.TV', 1, 1, 'CanalCosta#0');

-- ── Canal Coín [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Coín', 'https://graph.facebook.com/106272064368271/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Coín', @canal_id, 'https://canalcoin.garjim.es/hls/directo.m3u8', 441, 1, 'CanalCoin.TV', 1, 1, 'Canal Coín#0');

-- ── Canal Doñana [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Doñana', 'https://graph.facebook.com/donanacomunica/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Doñana', @canal_id, 'https://secure5.todostreaming.es/live/division-alm.m3u8', 441, 1, NULL, 1, 1, 'Canal Doñana#0');

-- ── Canal Extremadura [Spain / Extremadura] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Extremadura', 'https://graph.facebook.com/CanalExtremadura/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Extremadura', @canal_id, 'https://d2ymuyhevki1a1.cloudfront.net/wct-ddd2992e-86fc-4611-a491-7f392259e9ba/continuous/9be76611-d2cd-474d-bf19-ef702ba31b02/index.m3u8', 441, 1, 'Extremadura.TV', 1, 1, 'Canal Extremadura#0');

-- ── Canal IPe Perú [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal IPe Perú', 'https://graph.facebook.com/canalipe/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal IPe Perú', @canal_id, 'https://cdnhd.iblups.com/hls/3f2cb1658d114f2693eff18d83199e677.m3u8', NULL, 1, NULL, 1, 1, 'Canal IPe Perú#0');

-- ── Canal Kubo TV [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Kubo TV', 'https://yt3.ggpht.com/qUSCv44hJJ_C-k77dAwnCrNUqj30HYxD7zsNwZwe57POg1SPpLaBKi9JJ-_stu9GXkxPnnvlzo0=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Kubo TV', @canal_id, 'https://249807.global.ssl.fastly.net/68ce6783e24d0e8c2921aec8/live_877841b0996411f0a0647dde7e4bde90/index.m3u8', 441, 1, NULL, 1, 1, 'Canal Kubo TV#0');

-- ── Canal Luz Televisión [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Luz Televisión', 'https://graph.facebook.com/canalluztelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Luz Televisión', @canal_id, 'https://5f71743aa95e4.streamlock.net:1936/CanalLuz/enDirecto/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal Luz Televisión#0');

-- ── Canal Málaga [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Málaga', 'https://graph.facebook.com/CanalMalagaRTVMunicipal/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Málaga', @canal_id, 'https://canalmalaga-tv-live.flumotion.com/playlist.m3u8', 441, 1, 'CanalMalaga.TV', 1, 1, 'Canal Málaga#0');

-- ── Canal Once Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Once Mexico', 'https://graph.facebook.com/CANALONCETV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Once Mexico', @canal_id, 'https://vivo.canaloncelive.tv/oncedos/ngrp:pruebachunks_all/playlist.m3u8', 936, 1, NULL, 1, 1, 'Canal Once Mexico#0');

-- ── Canal Parlamento [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Parlamento', 'https://graph.facebook.com/CongresodelosDiputados/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Parlamento', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2037973/canalparlamento/master.m3u8', 441, 1, NULL, 1, 1, 'Canal Parlamento#0');

-- ── Canal Reus TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Reus TV', 'https://graph.facebook.com/canalreus.cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Reus TV', @canal_id, 'https://ingest2-video.streaming-pro.com/creus/stream/playlist.m3u8', 441, 1, 'Xarxa_Canal_Reus_TV.TV', 1, 1, 'Canal Reus TV#0');

-- ── Canal San Roque [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal San Roque', 'https://yt3.googleusercontent.com/6SgTMpyVCJlMGBcip6gvloYy2u-BP4vY-H2paJ2zO471owJq_YcgPhUUB0tBaKIKlNUKzeRf=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal San Roque', @canal_id, 'https://cdnlivevlc.codev8.net/aytosanroquelive/smil:channel1.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal San Roque#0');

-- ── Canal Sur 2 Accesible [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Sur 2 Accesible', 'https://graph.facebook.com/canalsurradioytv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Sur 2 Accesible', @canal_id, 'https://cdnlive.codev8.net/rtvalive/smil:channel22.smil/playlist.m3u8', 441, 1, 'CanalSurA.TV', 1, 1, 'Canal Sur 2 Accesible#0');

-- ── Canal Sur Andalucía [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Sur Andalucía', 'https://graph.facebook.com/canalsurradioytv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Sur Andalucía 1', @canal_id, 'https://live-24-canalsur.interactvty.pro/9bb0f4edcb8946e79f5017ddca6c02b0/26af5488cda642ed2eddd27a6328c93b9c03e9181b9d0a825147a7d978e69202.m3u8', 441, 1, 'CanalSurA.TV', 1, 1, 'Canal Sur Andalucía#0'),
('Canal Sur Andalucía 2', @canal_id, 'https://dfk2a268yviz9.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-ddiii1m6jt6of/CanalSurAndaluciaES.m3u8', 441, 1, 'CanalSurA.TV', 1, 1, 'Canal Sur Andalucía#1');

-- ── Canal Sur Más Noticias [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Sur Más Noticias', 'https://graph.facebook.com/CanalSurNoticias/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Sur Más Noticias', @canal_id, 'https://cdnlive.codev8.net/rtvalive/smil:channel42.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal Sur Más Noticias#0');

-- ── Canal Taronja Anoia [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Taronja Anoia', 'https://graph.facebook.com/canaltaronjaanoia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Taronja Anoia', @canal_id, 'https://ingest1-video.streaming-pro.com/canaltaronja/anoia/playlist.m3u8', 441, 1, 'Xarxa_Canal_Taronja_Anoia.TV', 1, 1, 'Canal Taronja Anoia#0');

-- ── Canal Taronja Central [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Taronja Central', 'https://graph.facebook.com/taronja.cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Taronja Central', @canal_id, 'https://ingest1-video.streaming-pro.com/canaltaronja/central/playlist.m3u8', 441, 1, 'Xarxa_Canal_Taronja_Central.TV', 1, 1, 'Canal Taronja Central#0');

-- ── Canal Taronja Osona i Moianés [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Taronja Osona i Moianés', 'https://graph.facebook.com/TaronjaTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Taronja Osona i Moianés', @canal_id, 'https://ingest1-video.streaming-pro.com/canaltaronja/osona/playlist.m3u8', 441, 1, NULL, 1, 1, 'Canal Taronja Osona i Moianés#0');

-- ── Canal Telecaribe Colombia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Telecaribe Colombia', 'https://graph.facebook.com/telecaribeEnvivo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Telecaribe Colombia 1', @canal_id, 'https://liveingesta118.cdnmedia.tv/telecaribetvlive/smil:rtmp01.smil/playlist.m3u8?DVR', NULL, 1, NULL, 1, 1, 'Canal Telecaribe Colombia#0'),
('Canal Telecaribe Colombia 2', @canal_id, 'https://liveingesta118.cdnmedia.tv/telecaribetvlive/smil:rtmp02.smil/playlist.m3u8?DVR', NULL, 1, NULL, 1, 1, 'Canal Telecaribe Colombia#1');

-- ── Canal Terrassa [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Terrassa', 'https://graph.facebook.com/canalterrassa/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Terrassa', @canal_id, 'https://ingest2-video.streaming-pro.com/canalterrassa/stream/playlist.m3u8', 441, 1, 'Xarxa_Canal_Terrassa.TV', 1, 1, 'Canal Terrassa#0');

-- ── Canal Terres de l'Ebre [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal Terres de l\'Ebre', 'https://graph.facebook.com/canal.terresdelebre/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal Terres de l\'Ebre', @canal_id, 'https://ingest1-video.streaming-pro.com/canalteABR/ctestream/playlist.m3u8', 441, 1, 'Xarxa_Canal_Terres_Ebre.TV', 1, 1, 'Canal Terres de l\'Ebre#0');

-- ── Canal TRO Colombia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Canal TRO Colombia', 'https://graph.facebook.com/canaltro/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Canal TRO Colombia', @canal_id, 'https://liveingesta118.cdnmedia.tv/canaltro2live/smil:live.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Canal TRO Colombia#0');

-- ── Cartaya TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cartaya TV', 'https://graph.facebook.com/radiocartaya/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cartaya TV', @canal_id, 'https://video3.lhdserver.es/cartayatv/live.m3u8', 441, 1, NULL, 1, 1, 'Cartaya TV#0');

-- ── Castilla-La Mancha Media [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Castilla-La Mancha Media', 'https://graph.facebook.com/CMMediaes/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Castilla-La Mancha Media', @canal_id, 'https://cdnapisec.kaltura.com/p/2288691/sp/228869100/playManifest/entryId/1_gnz6ity9/protocol/https/format/applehttp/a.m3u8', 441, 1, 'CMM.TV', 1, 1, 'Castilla-La Mancha Media#0');

-- ── Castilla-La Mancha Radio [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Castilla-La Mancha Radio', 'https://graph.facebook.com/RadioCLMes/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Castilla-La Mancha Radio', @canal_id, 'https://cdnapisec.kaltura.com/p/2288691/sp/228869100/playManifest/entryId/1_fmx3e3sd/protocol/https/format/applehttp/a.m3u8', 441, 1, NULL, 1, 1, 'Castilla-La Mancha Radio#0');

-- ── CBS News USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CBS News USA', 'https://graph.facebook.com/CBSNews/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CBS News USA', @canal_id, 'https://cbsn-us.cbsnstream.cbsnews.com/out/v1/55a8648e8f134e82a470f83d562deeca/master.m3u8', 542, 1, NULL, 1, 1, 'CBS News USA#0');

-- ── Cetelmon TV España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cetelmon TV España', 'https://graph.facebook.com/cetelmon.television/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cetelmon TV España', @canal_id, 'http://player.cetelmon.tv/protecteddfd43c2f3a8b41f3f28582bf8993aca6/992_high.m3u8', NULL, 1, NULL, 1, 1, 'Cetelmon TV España#0');

-- ── CGTN China [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CGTN China', 'https://graph.facebook.com/cgtnenespanol/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CGTN China 1', @canal_id, 'https://espanol-livews.cgtn.com/hls/LSveOGBaBw41Ea7ukkVAUdKQ220802LSTexu6xAuFH8VZNBLE1ZNEa220802cd/playlist.m3u8', NULL, 1, 'CGTN.TV', 1, 1, 'CGTN China#0'),
('CGTN China 2', @canal_id, 'https://news.cgtn.com/resource/live/espanol/cgtn-e.m3u8', NULL, 1, 'CGTN.TV', 1, 1, 'CGTN China#1');

-- ── CGTN Documentary China [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CGTN Documentary China', 'https://graph.facebook.com/ChinaGlobalTVNetwork/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CGTN Documentary China', @canal_id, 'https://english-livebkali.cgtn.com/live/doccgtn.m3u8', NULL, 1, NULL, 1, 1, 'CGTN Documentary China#0');

-- ── CGTN News China [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CGTN News China', 'https://graph.facebook.com/ChinaGlobalTVNetwork/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CGTN News China', @canal_id, 'https://english-livebkali.cgtn.com/live/encgtn.m3u8', NULL, 1, NULL, 1, 1, 'CGTN News China#0');

-- ── Channel 24 Ucrania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Channel 24 Ucrania', 'https://graph.facebook.com/news24ukraine/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Channel 24 Ucrania', @canal_id, 'http://streamvideol1.luxnet.ua/news24/news24.stream/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Channel 24 Ucrania#0');

-- ── Channel NewsAsia [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Channel NewsAsia', 'https://graph.facebook.com/ChannelNewsAsia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Channel NewsAsia', @canal_id, 'https://d2e1asnsl7br7b.cloudfront.net/7782e205e72f43aeb4a48ec97f66ebbe/index.m3u8', NULL, 1, NULL, 1, 1, 'Channel NewsAsia#0');

-- ── Cheddar USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cheddar USA', 'https://graph.facebook.com/cheddar/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cheddar USA', @canal_id, 'https://hls.livecdn.io/cheddar.com/cheddar/playlist.m3u8', 542, 1, NULL, 1, 1, 'Cheddar USA#0');

-- ── Ciudades Del Ocio TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ciudades Del Ocio TV', 'https://graph.facebook.com/CiudadesDelOcioTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ciudades Del Ocio TV', @canal_id, 'https://cloudvideo.servers10.com:8081/8024/index.m3u8', 441, 1, NULL, 1, 1, 'Ciudades Del Ocio TV#0');

-- ── Clan [Spain / Infantiles] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Clan', 'https://graph.facebook.com/clantve/picture?width=200&height=200', 5, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Clan 1', @canal_id, 'https://ztnr.rtve.es/ztnr/5466990.m3u8', 441, 1, 'Clan.TV', 1, 1, 'Clan#0'),
('Clan 2', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/clan/clan_main_dvr.m3u8', 441, 1, 'Clan.TV', 1, 1, 'Clan#1'),
('Clan 3', @canal_id, 'https://d1wca51iywzyn1.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-e2jakfg63mh4b/ClanES.m3u8', 441, 1, 'Clan.TV', 1, 1, 'Clan#2');

-- ── Classic Arts Showcase [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Classic Arts Showcase', 'https://pbs.twimg.com/profile_images/956583141245775872/2en3-8Ag_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Classic Arts Showcase', @canal_id, 'https://classicarts.akamaized.net/hls/live/1024257/CAS/master.m3u8', NULL, 1, NULL, 1, 1, 'Classic Arts Showcase#0');

-- ── CMC Croacia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CMC Croacia', 'https://graph.facebook.com/CroatianMusicChannel/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CMC Croacia', @canal_id, 'https://stream.cmctv.hr:49998/cmc/live.m3u8', NULL, 1, NULL, 1, 1, 'CMC Croacia#0');

-- ── CNN Internacional [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CNN Internacional', 'https://graph.facebook.com/cnninternational/picture?width=320&height=320', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CNN Internacional', @canal_id, 'https://ds2c506obo7m8.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-7zjq3tdqasbg8/index.m3u8', NULL, 1, 'CNNInt.TV', 1, 1, 'CNN Internacional#0');

-- ── Cocina (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cocina (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cocina (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/16656232.m3u8', 441, 1, 'RTVE_Cocina.TV', 1, 1, 'Cocina (RTVE)#0');

-- ── Cocina Familiar [Spain / La Rioja] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cocina Familiar', 'https://graph.facebook.com/cocinafamiliarjr/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cocina Familiar', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16114&live=1&avod=0&hls_marker=1&pod_duration=120&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&content_cat=IAB8&content_channel=cocinafamiliar&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&ssai_enabled=1&content_id=mirametv_live&min_ad_duration=6&max_ad_duration=120&app_domain=mirametv.live', 441, 1, NULL, 1, 1, 'Cocina Familiar#0');

-- ── Conciertos Radio 3 (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Conciertos Radio 3 (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Conciertos Radio 3 (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/6924117.m3u8', 441, 1, 'RTVE_Conciertos_R3.TV', 1, 1, 'Conciertos Radio 3 (RTVE)#0');

-- ── Condavisión [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Condavisión', 'https://graph.facebook.com/condavision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Condavisión', @canal_id, 'https://5f71743aa95e4.streamlock.net:1936/Condavision/endirecto/playlist.m3u8', 441, 1, 'Condavision.TV', 1, 1, 'Condavisión#0');

-- ── Congreso de los Diputados [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Congreso de los Diputados', 'https://graph.facebook.com/CongresodelosDiputados/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Congreso de los Diputados 1', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2038274/canal1/master.m3u8', 441, 1, NULL, 1, 1, 'Congreso de los Diputados#0'),
('Congreso de los Diputados 2', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2038275/canal2/master.m3u8', 441, 1, NULL, 1, 1, 'Congreso de los Diputados#1'),
('Congreso de los Diputados 3', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2038276/canal3/master.m3u8', 441, 1, NULL, 1, 1, 'Congreso de los Diputados#2'),
('Congreso de los Diputados 4', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2038277/canal4/master.m3u8', 441, 1, NULL, 1, 1, 'Congreso de los Diputados#3'),
('Congreso de los Diputados 5', @canal_id, 'https://congresodirecto.akamaized.net/hls/live/2038278/canal5/master.m3u8', 441, 1, NULL, 1, 1, 'Congreso de los Diputados#4');

-- ── Consejo de Gobierno de la Región de Murcia [Spain / R. de Murcia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Consejo de Gobierno de la Región de Murcia', 'https://graph.facebook.com/RegiondeMurciaRM/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Consejo de Gobierno de la Región de Murcia', @canal_id, 'https://crmlive.redctnet.es/liveedge/ConsejoGob/playlist.m3u8', 441, 1, NULL, 1, 1, 'Consejo de Gobierno de la Región de Murcia#0');

-- ── Cortes de Castilla y León [Spain / Castilla y León] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cortes de Castilla y León', 'https://graph.facebook.com/cortesdecastillayleon/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cortes de Castilla y León 1', @canal_id, 'https://directo.ccyl.es/Hemiciclo/smil:Hemiciclo.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Cortes de Castilla y León#0'),
('Cortes de Castilla y León 2', @canal_id, 'https://directo.ccyl.es/CortesDeLeon/smil:CortesDeLeon.smil/playlist.m3u8?DVR', 441, 1, NULL, 1, 1, 'Cortes de Castilla y León#1'),
('Cortes de Castilla y León 3', @canal_id, 'https://directo.ccyl.es/CamposDeCastilla/smil:CamposDeCastilla.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Cortes de Castilla y León#2'),
('Cortes de Castilla y León 4', @canal_id, 'https://directo.ccyl.es/CastilloDeFuensaldana/smil:CastilloDeFuensaldana.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Cortes de Castilla y León#3');

-- ── Corts Valencianes [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Corts Valencianes', 'https://graph.facebook.com/cortsval/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Corts Valencianes', @canal_id, 'https://streamserver3.seneca.tv/cval_live/cdn_enc_3/master.m3u8', 441, 1, NULL, 1, 1, 'Corts Valencianes#0');

-- ── Cosmos TV Perú [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cosmos TV Perú', 'https://pbs.twimg.com/profile_images/1904206504753811457/66CbqvH1_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cosmos TV Perú', @canal_id, 'https://videoserver.tmcreativos.com:19360/tvcosmos/tvcosmos.m3u8', NULL, 1, NULL, 1, 1, 'Cosmos TV Perú#0');

-- ── Costa Noroeste TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Costa Noroeste TV', 'https://graph.facebook.com/Costanoroestetv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Costa Noroeste TV', @canal_id, 'https://limited31.todostreaming.es/live/noroestetv-livestream.m3u8', 441, 1, 'CostaNO.TV', 1, 1, 'Costa Noroeste TV#0');

-- ── CourtTV USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('CourtTV USA', 'https://graph.facebook.com/courttv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('CourtTV USA', @canal_id, 'https://content.uplynk.com/channel/6c0bd0f94b1d4526a98676e9699a10ef.m3u8', 542, 1, NULL, 1, 1, 'CourtTV USA#0');

-- ── Cubavisión TV Cuba [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cubavisión TV Cuba', 'https://graph.facebook.com/CubavisionInternacional/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cubavisión TV Cuba', @canal_id, 'https://cdn.teveo.cu/live/video/A36pWmuWvZBQskuZ/ngrp:gppfydfzpSUn9Udy.stream/playlist.m3u8', NULL, 1, 'Cubavision.TV', 1, 1, 'Cubavisión TV Cuba#0');

-- ── Current Time TV [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Current Time TV', 'https://graph.facebook.com/currenttimetv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Current Time TV', @canal_id, 'https://rferl-ingest.akamaized.net/hls/live/2121657/tvmc05/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Current Time TV#0');

-- ── Cuéntame (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Cuéntame (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Cuéntame (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/6909843.m3u8', 441, 1, 'RTVE_Cuentame.TV', 1, 1, 'Cuéntame (RTVE)#0');

-- ── Dance TV Estonia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Dance TV Estonia', 'https://pbs.twimg.com/profile_images/1268129322730127364/OJlQBZpS_200x200.jpg', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Dance TV Estonia 1', @canal_id, 'https://m1b2.worldcast.tv/dancetelevisionone/2/dancetelevisionone.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#0'),
('Dance TV Estonia 2', @canal_id, 'https://m1b2.worldcast.tv/dancetelevisiontwo/2/dancetelevisiontwo.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#1'),
('Dance TV Estonia 3', @canal_id, 'https://m2b2.worldcast.tv:7443/dancetelevisionthree/2/dancetelevisionthree.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#2'),
('Dance TV Estonia 4', @canal_id, 'https://m2b2.worldcast.tv:7443/dancetelevisionfour/2/dancetelevisionfour.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#3'),
('Dance TV Estonia 5', @canal_id, 'https://m2b2.worldcast.tv:7443/dancetelevisionfive/2/dancetelevisionfive.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#4'),
('Dance TV Estonia 6', @canal_id, 'https://mbit1.worldcast.tv/dancetelevisionsix/multibit.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#5'),
('Dance TV Estonia 7', @canal_id, 'https://mbit1.worldcast.tv/dancetelevisionseven/multibit.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#6'),
('Dance TV Estonia 8', @canal_id, 'https://mbit1.worldcast.tv:9443/dancetelevisioneight/multibit.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#7'),
('Dance TV Estonia 9', @canal_id, 'https://mbit1.worldcast.tv:9443/dancetelevisionnine/multibit.m3u8', NULL, 1, NULL, 1, 1, 'Dance TV Estonia#8');

-- ── De Laredu Lin TV [Spain / Cantabria] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('De Laredu Lin TV', 'https://graph.facebook.com/delaredulintv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('De Laredu Lin TV', @canal_id, 'https://eu1.servers10.com:8081/8034/index.m3u8', 441, 1, NULL, 1, 1, 'De Laredu Lin TV#0');

-- ── Diez TV Andújar [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Diez TV Andújar', 'https://graph.facebook.com/dieztv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Diez TV Andújar', @canal_id, 'https://streamtv2.elitecomunicacion.cloud:3305/live/10tvandujarlive.m3u8', 441, 1, 'DiezTV.TV', 1, 1, 'Diez TV Andújar#0');

-- ── Diez TV Las Villas [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Diez TV Las Villas', 'https://graph.facebook.com/dieztv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Diez TV Las Villas', @canal_id, 'https://streaming.cloud.innovasur.es/mmj2/index.m3u8', 441, 1, 'DiezTV.TV', 1, 1, 'Diez TV Las Villas#0');

-- ── Diez TV Úbeda [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Diez TV Úbeda', 'https://graph.facebook.com/dieztv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Diez TV Úbeda', @canal_id, 'https://streaming.cloud.innovasur.es/mmj/index.m3u8', 441, 1, 'DiezTV.TV', 1, 1, 'Diez TV Úbeda#0');

-- ── Digi24 Rumanía [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Digi24 Rumanía', 'https://graph.facebook.com/Digi24HD/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Digi24 Rumanía', @canal_id, 'https://pubads.g.doubleclick.net/ssai/event/OQfdjUhHSDSlb1fJVzehsQ/master.m3u8', NULL, 1, NULL, 1, 1, 'Digi24 Rumanía#0');

-- ── Distrito TV [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Distrito TV', 'https://graph.facebook.com/2004860103163343/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Distrito TV', @canal_id, 'https://live.emitstream.com/hls/3mn7wpcv7hbmxmdzaxap/master.m3u8', 441, 1, 'Distrito.TV', 1, 1, 'Distrito TV#0');

-- ── Ditty TV USA [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ditty TV USA', 'https://graph.facebook.com/DittyTV/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ditty TV USA', @canal_id, 'https://0ba805a2403b4660bbb05c0a210ebbdc.mediatailor.us-east-1.amazonaws.com/v1/master/04fd913bb278d8775298c26fdca9d9841f37601f/ONO_DittyTV/playlist.m3u8', 542, 1, NULL, 1, 1, 'Ditty TV USA#0');

-- ── Durangaldeko TV [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Durangaldeko TV', 'https://graph.facebook.com/dotbDurangaldekoTelebista/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Durangaldeko TV', @canal_id, 'https://live.emitstream.com/hls/5f9asjsehd7gmyxsdpzu/master.m3u8', 441, 1, NULL, 1, 1, 'Durangaldeko TV#0');

-- ── DW Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('DW Alemania', 'https://graph.facebook.com/dw.espanol/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('DW Alemania 1', @canal_id, 'https://dwamdstream104.akamaized.net/hls/live/2015530/dwstream104/index.m3u8', 723, 1, 'DW.TV', 1, 1, 'DW Alemania#0'),
('DW Alemania 2', @canal_id, 'https://dwamdstream102.akamaized.net/hls/live/2015525/dwstream102/index.m3u8', 723, 1, 'DW.TV', 1, 1, 'DW Alemania#1'),
('DW Alemania 3', @canal_id, 'https://dwamdstream106.akamaized.net/hls/live/2017965/dwstream106/index.m3u8', 723, 1, 'DW.TV', 1, 1, 'DW Alemania#2');

-- ── Déjate de Historias TV [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Déjate de Historias TV', 'https://graph.facebook.com/DejateDeHistoriasTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Déjate de Historias TV', @canal_id, 'https://limited44.todostreaming.es/live/dejatedeh-livestream.m3u8', 441, 1, 'Dejate.TV', 1, 1, 'Déjate de Historias TV#0');

-- ── Ecclesia COPE España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ecclesia COPE España', 'https://graph.facebook.com/ecclesiacope/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ecclesia COPE España', @canal_id, 'https://cope-religion-video.flumotion.com/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Ecclesia COPE España#0');

-- ── Ecuador TV Ecuador [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ecuador TV Ecuador', 'https://pbs.twimg.com/profile_images/1962545090485784576/uXP9DhVk_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ecuador TV Ecuador', @canal_id, 'https://samson.streamerr.co:8081/shogun/tracks-v1a1/mono.m3u8', NULL, 1, NULL, 1, 1, 'Ecuador TV Ecuador#0');

-- ── Elche 7 TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Elche 7 TV', 'https://graph.facebook.com/Elche7HD/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Elche 7 TV', @canal_id, 'https://elche7tv.gestec-video.com/hls/canal2.m3u8', 441, 1, 'Elche7.TV', 1, 1, 'Elche 7 TV#0');

-- ── El Confidencial [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('El Confidencial', 'https://graph.facebook.com/elconfidencial/picture?width=200&height=200', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('El Confidencial', @canal_id, 'https://d2x0ptujt9cf22.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/pb-bsk8edpvt6m4t/master.m3u8', 441, 1, 'ElConfidencial.TV', 1, 1, 'El Confidencial#0');

-- ── El País [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('El País', 'https://graph.facebook.com/elpais/picture?width=200&height=200', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('El País', @canal_id, 'https://d2epgk1fomaa1g.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-9n8y4tw0bk3an/live/fast-channel-el-pais/fast-channel-el-pais.m3u8', 441, 1, 'ElPais.TV', 1, 1, 'El País#0');

-- ── El Toro TV [Spain / Generalistas] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('El Toro TV', 'https://graph.facebook.com/eltorotv.es/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('El Toro TV 1', @canal_id, 'https://streaming-1.eltorotv.com/lb0/eltorotv-streaming-web/index.m3u8', 441, 1, 'ElToroTV.TV', 1, 1, 'El Toro TV#0'),
('El Toro TV 2', @canal_id, 'https://edge-nodo-002.streaming.hitcloser.net/eltorotv-streaming-web/index.m3u8', 441, 1, 'ElToroTV.TV', 1, 1, 'El Toro TV#1');

-- ── Empordà TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Empordà TV', 'https://graph.facebook.com/empordatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Empordà TV', @canal_id, 'https://video3.lhdserver.es/empordatv2/live.m3u8', 441, 1, 'Xarxa_EmpordaTV.TV', 1, 1, 'Empordà TV#0');

-- ── EnerGeek TV [Spain / Infantiles] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('EnerGeek TV', 'https://graph.facebook.com/EnerGeekTelevision/picture?width=200&height=200', 5, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('EnerGeek TV 1', @canal_id, 'https://backend.energeek.cl/webtv/egretro/mobile/index.m3u8?token=dEmoweBeneRGEek2025', 441, 1, NULL, 1, 1, 'EnerGeek TV#0'),
('EnerGeek TV 2', @canal_id, 'https://backend.energeek.cl/webtv/egretroweb/index.m3u8?token=dEmoweBeneRGEek2025', 441, 1, NULL, 1, 1, 'EnerGeek TV#1');

-- ── Enlace TV Costa Rica [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Enlace TV Costa Rica', 'https://graph.facebook.com/enlacetv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Enlace TV Costa Rica', @canal_id, 'https://livecdn.enlace.plus/enlace/smil:enlace-hd.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Enlace TV Costa Rica#0');

-- ── En Play (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('En Play (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('En Play (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/17017351.m3u8', 441, 1, 'RTVE_En_Play.TV', 1, 1, 'En Play (RTVE)#0');

-- ── Esport 3 [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Esport 3', 'https://graph.facebook.com/Esport3/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Esport 3 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-origin/esport3-hls/master.m3u8', 441, 1, 'E3.TV', 1, 1, 'Esport 3#0'),
('Esport 3 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-origin/esport3-hls/master.m3u8', 441, 1, 'E3.TV', 1, 1, 'Esport 3#1');

-- ── Este Canal TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Este Canal TV', 'https://graph.facebook.com/estecanaltv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Este Canal TV', @canal_id, 'http://synclosdragos1.syncsolutions.es:8008/live3/emision/index.m3u8', 441, 1, NULL, 1, 1, 'Este Canal TV#0');

-- ── Estepona TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Estepona TV', 'https://graph.facebook.com/esteponatelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Estepona TV', @canal_id, 'https://cloudvideo.servers10.com:8081/8022/index.m3u8', 441, 1, NULL, 1, 1, 'Estepona TV#0');

-- ── Estrella TV Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Estrella TV Mexico', 'https://graph.facebook.com/EstrellaTVNetwork/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Estrella TV Mexico 1', @canal_id, 'https://cdn-uw2-prod.tsv2.amagi.tv/linear/amg00567-estrellamedia-estrellatv-estrellamedia/playlist.m3u8', 936, 1, NULL, 1, 1, 'Estrella TV Mexico#0'),
('Estrella TV Mexico 2', @canal_id, 'https://cdn-uw2-prod.tsv2.amagi.tv/linear/amg00567-estrellamedia-estrellanews-estrellamedia/playlist.m3u8', 936, 1, NULL, 1, 1, 'Estrella TV Mexico#1'),
('Estrella TV Mexico 3', @canal_id, 'https://cdn-uw2-prod.tsv2.amagi.tv/linear/amg00567-estrellamedia-estrellagames-estrellamedia/playlist.m3u8', 936, 1, NULL, 1, 1, 'Estrella TV Mexico#2');

-- ── EsTuTele [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('EsTuTele', 'https://graph.facebook.com/Estutele/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('EsTuTele', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=16818&live=1&avod=1&cb=[CACHEBUSTER]&site_page=https%3A%2F%2Ftdtchannels.com&site_name=tdtchannels&hls_marker=1&content_cat=IAB1&content_genre=Entertainment&content_id=Estutele&content_language=es&content_rating=TV-G&content_title=Estutele&coppa=0&ssai_enabled=1', 441, 1, 'EsTuTele.TV', 1, 1, 'EsTuTele#0');

-- ── ETB 1 [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB 1', 'https://graph.facebook.com/eitb/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB 1', @canal_id, 'https://cdn1.etbon.eus/etb1/index.m3u8', 441, 1, 'ETB1.TV', 1, 1, 'ETB 1#0');

-- ── ETB1 ON [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB1 ON', 'https://play-lh.googleusercontent.com/GUW-ipQpsCCLhoJwWarfUDYO_vr3-5rpxhfipNSHAAvlaaWdfBwdUtVVUzs3PPyQzrSBVepKSqPzNAwDHvljII0=w200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB1 ON', @canal_id, 'https://cdn1.etbon.eus/etb1on/index.m3u8', 441, 1, 'ETB1ON.TV', 1, 1, 'ETB1 ON#0');

-- ── ETB 2 [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB 2', 'https://graph.facebook.com/eitb/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB 2', @canal_id, 'https://cdn1.etbon.eus/etb2/index.m3u8', 441, 1, 'ETB2.TV', 1, 1, 'ETB 2#0');

-- ── ETB2 ON [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB2 ON', 'https://play-lh.googleusercontent.com/GUW-ipQpsCCLhoJwWarfUDYO_vr3-5rpxhfipNSHAAvlaaWdfBwdUtVVUzs3PPyQzrSBVepKSqPzNAwDHvljII0=w200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB2 ON', @canal_id, 'https://cdn1.etbon.eus/etb2on/index.m3u8', 441, 1, 'ETB2ON.TV', 1, 1, 'ETB2 ON#0');

-- ── ETB Deportes [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB Deportes', 'https://graph.facebook.com/deportes.eitb.kirolak/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB Deportes 1', @canal_id, 'https://multimedia.eitb.eus/live-content/oka1hd-hls/master.m3u8', 441, 1, 'ETBD.TV', 1, 1, 'ETB Deportes#0'),
('ETB Deportes 2', @canal_id, 'https://multimedia.eitb.eus/live-content/oka2hd-hls/master.m3u8', 441, 1, 'ETBD.TV', 1, 1, 'ETB Deportes#1'),
('ETB Deportes 3', @canal_id, 'https://multimedia.eitb.eus/live-content/oka3hd-hls/master.m3u8', 441, 1, 'ETBD.TV', 1, 1, 'ETB Deportes#2');

-- ── ETB Eventos 1 [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB Eventos 1', 'https://graph.facebook.com/eitb/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB Eventos 1', @canal_id, 'https://cdn1.etbon.eus/oc1/index.m3u8', 441, 1, 'ETBON_Oca1.TV', 1, 1, 'ETB Eventos 1#0');

-- ── ETB Eventos 2 [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ETB Eventos 2', 'https://graph.facebook.com/eitb/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ETB Eventos 2', @canal_id, 'https://cdn1.etbon.eus/oc2/index.m3u8', 441, 1, 'ETBON_Oca2.TV', 1, 1, 'ETB Eventos 2#0');

-- ── etv [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('etv', 'https://graph.facebook.com/etv.llobregat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('etv', @canal_id, 'https://liveingesta318.cdnmedia.tv/tvetvlive/smil:rtmp01.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_ETV.TV', 1, 1, 'etv#0');

-- ── Euronews [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Euronews', 'https://graph.facebook.com/es.euronews/picture?width=200&height=200', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Euronews', @canal_id, 'https://euronews-live-spa-es.fast.rakuten.tv/v1/master/0547f18649bd788bec7b67b746e47670f558b6b2/production-LiveChannel-6571/bitok/eyJzdGlkIjoiMDA0YjY0NTMtYjY2MC00ZTZkLTlkNzEtMTk3YTM3ZDZhZWIxIiwibWt0IjoiZXMiLCJjaCI6NjU3MSwicHRmIjoxfQ==/26034/euronews-es.m3u8', 441, 1, 'Euronews.TV', 1, 1, 'Euronews#0');

-- ── Euskadi Meteo (ETB On) [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Euskadi Meteo (ETB On)', 'https://play-lh.googleusercontent.com/GUW-ipQpsCCLhoJwWarfUDYO_vr3-5rpxhfipNSHAAvlaaWdfBwdUtVVUzs3PPyQzrSBVepKSqPzNAwDHvljII0=w200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Euskadi Meteo (ETB On)', @canal_id, 'https://cdn1.etbon.eus/meteo/index.m3u8', 441, 1, NULL, 1, 1, 'Euskadi Meteo (ETB On)#0');

-- ── Eusko Legebiltzarra [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Eusko Legebiltzarra', 'https://graph.facebook.com/legebiltzarra/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Eusko Legebiltzarra 1', @canal_id, 'https://bideoak.legebiltzarra.eus/zuzenean/stream-3_channel-1/playlist.m3u8', 441, 1, NULL, 1, 1, 'Eusko Legebiltzarra#0'),
('Eusko Legebiltzarra 2', @canal_id, 'https://bideoak.legebiltzarra.eus/zuzenean/stream-3_channel-2/playlist.m3u8', 441, 1, NULL, 1, 1, 'Eusko Legebiltzarra#1');

-- ── EWTN [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('EWTN', 'https://graph.facebook.com/ewtnespanol/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('EWTN 1', @canal_id, 'https://cdn3.wowza.com/1/SmVrQmZCUXZhVDgz/b3J3MFJv/hls/live/playlist.m3u8', NULL, 1, 'EWTN.TV', 1, 1, 'EWTN#0'),
('EWTN 2', @canal_id, 'https://cdn3.wowza.com/1/YW5wSWZiRGd2eFlU/bGV0aVBq/hls/live/playlist.m3u8', NULL, 1, 'EWTN.TV', 1, 1, 'EWTN#1');

-- ── Exitosa Noticias Perú [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Exitosa Noticias Perú', 'https://graph.facebook.com/Exitosanoticias/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Exitosa Noticias Perú', @canal_id, 'https://luna-4-video.mediaserver.digital/exitosatv_233b-4b49-a726-5a451262/index.m3u8', NULL, 1, NULL, 1, 1, 'Exitosa Noticias Perú#0');

-- ── Expres Chequia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Expres Chequia', 'https://graph.facebook.com/OckoExpres/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Expres Chequia', @canal_id, 'https://ocko-live.ssl.cdn.cra.cz/channels/ocko_expres/playlist/cze/live_hq.m3u8', NULL, 1, NULL, 1, 1, 'Expres Chequia#0');

-- ── Factoría de Carnaval [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Factoría de Carnaval', 'https://pbs.twimg.com/profile_images/1498617906560737281/iOri7Ujk_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Factoría de Carnaval', @canal_id, 'https://eu1.servers10.com:8081/8116/index.m3u8', 441, 1, NULL, 1, 1, 'Factoría de Carnaval#0');

-- ── Fashion TV [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Fashion TV', 'https://graph.facebook.com/FTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Fashion TV', @canal_id, 'https://ftv1.b-cdn.net/bfdbb576-83f7-11f0-9f89-0200170e3e04_1000028043_HLS/manifest.m3u8', NULL, 1, NULL, 1, 1, 'Fashion TV#0');

-- ── Fibwi Diario [Spain / Illes Balears] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Fibwi Diario', 'https://pbs.twimg.com/profile_images/1937439289270288384/qFK2qqCW_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Fibwi Diario', @canal_id, 'https://hostcdn3.fibwi.com/fibwi_diario/index.fmp4.m3u8', 441, 1, NULL, 1, 1, 'Fibwi Diario#0');

-- ── FOX Live Now USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('FOX Live Now USA', 'https://graph.facebook.com/livenowfox/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('FOX Live Now USA', @canal_id, 'https://fox-foxnewsnow-samsungus.amagi.tv/playlist.m3u8', 542, 1, NULL, 1, 1, 'FOX Live Now USA#0');

-- ── Fuengirola TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Fuengirola TV', 'https://graph.facebook.com/fuengirolatvoficial/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Fuengirola TV', @canal_id, 'https://streaming004.gestec-video.com/hls/FTV.m3u8', 441, 1, 'FuengirolaTV.TV', 1, 1, 'Fuengirola TV#0');

-- ── Fuerteventura TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Fuerteventura TV', 'https://yt3.ggpht.com/qj92f7GsPI7R-YCYzsFj5mDoSCduHSgh8lwCWHFXbHAx6rNmLsB78RZlmfiqbjYzQdNh1Fj9sQ=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Fuerteventura TV', @canal_id, 'https://5c0956165db0b.streamlock.net/ftv/directo/.m3u8', 441, 1, NULL, 1, 1, 'Fuerteventura TV#0');

-- ── Futsalmafer.tv [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Futsalmafer.tv', 'https://graph.facebook.com/futsalmafer.tv/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Futsalmafer.tv', @canal_id, 'https://play.agenciastreaming.com:8081/futsalmafertv/index.m3u8', 441, 1, NULL, 1, 1, 'Futsalmafer.tv#0');

-- ── Garage TV Argentina [International / Deportivos Int.] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Garage TV Argentina', 'https://pbs.twimg.com/profile_images/1169992187314167808/TeabGtEB_200x200.jpg', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Garage TV Argentina', @canal_id, 'https://stream1.sersat.com/hls/garagetv.m3u8', 896, 1, NULL, 1, 1, 'Garage TV Argentina#0');

-- ── GB News Reino Unido [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('GB News Reino Unido', 'https://graph.facebook.com/GBNewsOnline/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('GB News Reino Unido', @canal_id, 'https://amg01076-lightningintern-gbnews-samsunguk-0lu52.amagi.tv/playlist/amg01076-lightningintern-gbnews-samsunguk/playlist.m3u8', NULL, 1, NULL, 1, 1, 'GB News Reino Unido#0');

-- ── GCM Internacional [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('GCM Internacional', 'https://pbs.twimg.com/profile_images/1752299087402041344/eAHH3L02_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('GCM Internacional', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12128&live=1', NULL, 1, NULL, 1, 1, 'GCM Internacional#0');

-- ── Geo News Pakistan [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Geo News Pakistan', 'https://graph.facebook.com/GeoUrduDotTv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Geo News Pakistan', @canal_id, 'https://jk3lz82elw79-hls-live.5centscdn.com/newgeonews/07811dc6c422334ce36a09ff5cd6fe71.sdp/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Geo News Pakistan#0');

-- ── Globovision Venezuela [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Globovision Venezuela', 'https://graph.facebook.com/globovision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Globovision Venezuela', @canal_id, 'https://59d39900ebfb8.streamlock.net/globo-720p/globo-720p/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Globovision Venezuela#0');

-- ── Goiena Eus [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Goiena Eus', 'https://graph.facebook.com/goiena.eus/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Goiena Eus', @canal_id, 'https://zuzenean.goienamedia.eus/goiena-telebista.m3u8', 441, 1, NULL, 1, 1, 'Goiena Eus#0');

-- ── Goierri Irrati TV [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Goierri Irrati TV', 'https://graph.facebook.com/GoierriIrratiTelebista/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Goierri Irrati TV', @canal_id, 'https://streaming.gitb.eus/hls/z.m3u8', 441, 1, NULL, 1, 1, 'Goierri Irrati TV#0');

-- ── Guada TV Media [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Guada TV Media', 'https://graph.facebook.com/GuadaTV.TV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Guada TV Media', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12689&live=1', 441, 1, NULL, 1, 1, 'Guada TV Media#0');

-- ── GUKA TB [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('GUKA TB', 'https://graph.facebook.com/guka.eus/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('GUKA TB', @canal_id, 'https://streaming.ukt.eus/hls/test.m3u8', 441, 1, NULL, 1, 1, 'GUKA TB#0');

-- ── Hamaika TV [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Hamaika TV', 'https://graph.facebook.com/HamaikaTb/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Hamaika TV', @canal_id, 'https://cdn3.wowza.com/1/RERMR282dnU5eE5Z/OHY0dVFs/hls/live/playlist.m3u8', 441, 1, NULL, 1, 1, 'Hamaika TV#0');

-- ── Hispan TV Iran [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Hispan TV Iran', 'https://pbs.twimg.com/profile_images/1645382928422109184/lUdeHBAs_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Hispan TV Iran', @canal_id, 'https://live.presstv.ir/hls/hispantv_5_482/index.m3u8', NULL, 1, NULL, 1, 1, 'Hispan TV Iran#0');

-- ── HR Hessenschau Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('HR Hessenschau Alemania', 'https://graph.facebook.com/Hessenschau/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('HR Hessenschau Alemania', @canal_id, 'https://hr-live.ard-mcdn.de/hr/live/hls/de/master.m3u8', 723, 1, NULL, 1, 1, 'HR Hessenschau Alemania#0');

-- ── Imás TV [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Imás TV', 'https://graph.facebook.com/television.imas/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Imás TV', @canal_id, 'https://secure3.todostreaming.es/live/imastv-livestream.m3u8', 441, 1, 'Imas.TV', 1, 1, 'Imás TV#0');

-- ── India Today [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('India Today', 'https://graph.facebook.com/IndiaToday/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('India Today 1', @canal_id, 'https://indiatodaylive.akamaized.net/hls/live/2014320/indiatoday/indiatodaylive/playlist.m3u8', NULL, 1, NULL, 1, 1, 'India Today#0'),
('India Today 2', @canal_id, 'https://feeds.intoday.in/aajtak/api/master.m3u8', NULL, 1, NULL, 1, 1, 'India Today#1');

-- ── Infantil (Canal Extremadura) [Spain / Infantiles] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Infantil (Canal Extremadura)', 'https://graph.facebook.com/CanalExtremadura/picture?width=200&height=200', 5, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Infantil (Canal Extremadura)', @canal_id, 'https://cdn-canalextremadura.watchity.net/fast2/master.m3u8', 441, 1, NULL, 1, 1, 'Infantil (Canal Extremadura)#0');

-- ── Int. Table Soccer Federation [International / Deportivos Int.] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Int. Table Soccer Federation', 'https://graph.facebook.com/ITSF.tablesoccer/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Int. Table Soccer Federation', @canal_id, 'https://stream.ads.ottera.tv/playlist.m3u8?network_id=7333', NULL, 1, NULL, 1, 1, 'Int. Table Soccer Federation#0');

-- ── Interalmería TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Interalmería TV', 'https://graph.facebook.com/Interalmeriatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Interalmería TV', @canal_id, 'https://interalmeria.tv/directo/live.m3u8', 441, 1, 'InterAlmeria.TV', 1, 1, 'Interalmería TV#0');

-- ── Intercomarcal TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Intercomarcal TV', 'https://graph.facebook.com/Intercomarcal.Television/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Intercomarcal TV 1', @canal_id, 'https://streamingtvi.gestec-video.com/hls/tvixa.m3u8', 441, 1, NULL, 1, 1, 'Intercomarcal TV#0'),
('Intercomarcal TV 2', @canal_id, 'https://streamingtvi.gestec-video.com/hls/tvixa1.m3u8', 441, 1, NULL, 1, 1, 'Intercomarcal TV#1'),
('Intercomarcal TV 3', @canal_id, 'https://streamingtvi.gestec-video.com/hls/tvixa3.m3u8', 441, 1, NULL, 1, 1, 'Intercomarcal TV#2');

-- ── iPROtv [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('iPROtv', 'https://graph.facebook.com/iprotvspain/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('iPROtv', @canal_id, 'https://59ec5453559f0.streamlock.net/iprotv/iprotv/playlist.m3u8', 441, 1, NULL, 1, 1, 'iPROtv#0');

-- ── Junta Castilla y León [Spain / Castilla y León] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Junta Castilla y León', 'https://graph.facebook.com/juntadecastillayleon/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Junta Castilla y León', @canal_id, 'https://16escalones-live2.flumotion.com/chunks.m3u8', 441, 1, NULL, 1, 1, 'Junta Castilla y León#0');

-- ── Junta General del Principado de Asturias [Spain / P. de Asturias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Junta General del Principado de Asturias', 'https://pbs.twimg.com/profile_images/2030898420681232384/TBZNbHLJ_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Junta General del Principado de Asturias', @canal_id, 'https://wmserver.jgpa.es/live/_definst_/livestream2/playlist.m3u8', 441, 1, NULL, 1, 1, 'Junta General del Principado de Asturias#0');

-- ── Kronehit Austria [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Kronehit Austria', 'https://graph.facebook.com/kronehit/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Kronehit Austria', @canal_id, 'https://bitcdn-kronehit.bitmovin.com/v2/hls/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Kronehit Austria#0');

-- ── L'Hospitalet TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('L\'Hospitalet TV', 'https://graph.facebook.com/lhdigital/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('L\'Hospitalet TV', @canal_id, 'https://liveingesta318.cdnmedia.tv/tvhospitaletlive/smil:tvhospitalet.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_Televisio_Hospitalet.TV', 1, 1, 'L\'Hospitalet TV#0');

-- ── La 1 [Spain / Generalistas] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 1', 'https://pbs.twimg.com/profile_images/2008842210414915584/zIp_go25_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 1 1', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/la1/la1_main_dvr.m3u8', 441, 1, 'La1.TV', 1, 1, 'La 1#0'),
('La 1 2', @canal_id, 'https://stream.ads.ottera.tv/playlist.m3u8?network_id=15619', 441, 1, 'La1.TV', 1, 1, 'La 1#1');

-- ── La 1 Canarias [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 1 Canarias', 'https://pbs.twimg.com/profile_images/2008842210414915584/zIp_go25_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 1 Canarias', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/can/la1_can_main_dvr.m3u8', 441, 1, 'La1_CAN.TV', 1, 1, 'La 1 Canarias#0');

-- ── La 1 Catalunya [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 1 Catalunya', 'https://pbs.twimg.com/profile_images/2008842210414915584/zIp_go25_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 1 Catalunya', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/cat/la1_cat_main_dvr.m3u8', 441, 1, 'La1_CAT.TV', 1, 1, 'La 1 Catalunya#0');

-- ── La 2 [Spain / Generalistas] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 2', 'https://yt3.googleusercontent.com/ytc/AIdro_kqgHWySi5xprs1VFCNCX0IKNT8yXBLZC43JMoB8j0JUto=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 2 1', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/la2/la2_main_dvr.m3u8', 441, 1, 'La2.TV', 1, 1, 'La 2#0'),
('La 2 2', @canal_id, 'https://d1yebix5w29z3v.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-haqfba85d1gvv/La2ES.m3u8', 441, 1, 'La2.TV', 1, 1, 'La 2#1');

-- ── La 2 Canarias [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 2 Canarias', 'https://yt3.googleusercontent.com/ytc/AIdro_kqgHWySi5xprs1VFCNCX0IKNT8yXBLZC43JMoB8j0JUto=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 2 Canarias', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/can/la2_can_main_dvr.m3u8', 441, 1, 'La2_CAN.TV', 1, 1, 'La 2 Canarias#0');

-- ── La 2 Catalunya [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 2 Catalunya', 'https://yt3.googleusercontent.com/ytc/AIdro_kqgHWySi5xprs1VFCNCX0IKNT8yXBLZC43JMoB8j0JUto=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 2 Catalunya', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/cat/la2_cat_main_dvr.m3u8', 441, 1, 'La2_CAT.TV', 1, 1, 'La 2 Catalunya#0');

-- ── La7 Italia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La7 Italia', 'https://graph.facebook.com/tgla7/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La7 Italia', @canal_id, 'https://d1chghleocc9sm.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-evfku205gqrtf/Live.m3u8', 408, 1, NULL, 1, 1, 'La7 Italia#0');

-- ── La 8 Mediterráneo [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La 8 Mediterráneo', 'https://graph.facebook.com/la8mediterraneo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La 8 Mediterráneo', @canal_id, 'https://newscript.gestec-video.com/hls/8TVEVENTOS.m3u8', 441, 1, '8M.TV', 1, 1, 'La 8 Mediterráneo#0');

-- ── La Mega Mundial USA [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La Mega Mundial USA', 'https://graph.facebook.com/lamegaworldwide/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La Mega Mundial USA', @canal_id, 'https://server40.servistreaming.com:3477/stream/play.m3u8', 542, 1, NULL, 1, 1, 'La Mega Mundial USA#0');

-- ── Lancelot TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Lancelot TV', 'https://graph.facebook.com/LancelotTelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Lancelot TV', @canal_id, 'https://5c0956165db0b.streamlock.net:8090/directo/_definst_/lancelot.television/master.m3u8', 441, 1, NULL, 1, 1, 'Lancelot TV#0');

-- ── La promesa (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La promesa (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La promesa (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/2472039.m3u8', 441, 1, 'RTVE_Promesa.TV', 1, 1, 'La promesa (RTVE)#0');

-- ── La Revuelta (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('La Revuelta (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('La Revuelta (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/16464388.m3u8', 441, 1, 'RTVE_Epoca.TV', 1, 1, 'La Revuelta (RTVE)#0');

-- ── Latina Perú [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Latina Perú', 'https://graph.facebook.com/Latina.pe/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Latina Perú 1', @canal_id, 'https://redirector.rudo.video/hls-video/567ffde3fa319fadf3419efda25619456231dfea/latina/latina.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Latina Perú#0'),
('Latina Perú 2', @canal_id, 'https://redirector.rudo.video/hls-video/567ffde3fa319fadf3419efda25619456231dfea/latinanoticias/latinanoticias.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Latina Perú#1'),
('Latina Perú 3', @canal_id, 'https://redirector.rudo.video/hls-video/plus226/latina2/latina2.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Latina Perú#2');

-- ── LIRA TV [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('LIRA TV', 'https://graph.facebook.com/liratvlive/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('LIRA TV', @canal_id, 'https://cloud2.streaminglivehd.com:1936/liratv/liratv/playlist.m3u8', 441, 1, NULL, 1, 1, 'LIRA TV#0');

-- ── Lleida TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Lleida TV', 'https://graph.facebook.com/LleidaTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Lleida TV', @canal_id, 'https://liveingesta318.cdnmedia.tv/lleidatvlive/smil:live.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_LleidaTV.TV', 1, 1, 'Lleida TV#0');

-- ── m2o Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('m2o Italia', 'https://graph.facebook.com/radiom2o/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('m2o Italia', @canal_id, 'https://4c4b867c89244861ac216426883d1ad0.msvdn.net/live/S62628868/uhdWBlkC1AoO/playlist.m3u8', 408, 1, NULL, 1, 1, 'm2o Italia#0');

-- ── M95 Marbella [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('M95 Marbella', 'https://graph.facebook.com/m95tvmarbella/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('M95 Marbella', @canal_id, 'https://limited2.todostreaming.es/live/m95-livestream.m3u8', 441, 1, NULL, 1, 1, 'M95 Marbella#0');

-- ── Maestrat TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Maestrat TV', 'https://graph.facebook.com/maestrat.tv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Maestrat TV', @canal_id, 'https://stream.maestrat.tv/hls/stream.m3u8', 441, 1, NULL, 1, 1, 'Maestrat TV#0');

-- ── Manilva TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Manilva TV', 'https://graph.facebook.com/rtvmanilva/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Manilva TV', @canal_id, 'https://stream.castr.com/627a72d21914543be01c1720/live_e2ae1780dc2a11eca660b7b17b7952a5/tracks-v1a1/mono.m3u8', 441, 1, NULL, 1, 1, 'Manilva TV#0');

-- ── Marbella TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Marbella TV', 'https://graph.facebook.com/RTVMarbella/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Marbella TV', @canal_id, 'https://streaming.rtvmarbella.tv/hls/streamingweb.m3u8', 441, 1, 'MarbellaTV.TV', 1, 1, 'Marbella TV#0');

-- ── Mar TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Mar TV', 'https://graph.facebook.com/martelevisio/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Mar TV', @canal_id, 'https://rfe-ingest.akamaized.net/hls/live/2033043/tvmc05/master.m3u8', 441, 1, NULL, 1, 1, 'Mar TV#0');

-- ── María Visión España y Mexico [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('María Visión España y Mexico', 'https://graph.facebook.com/mariavision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('María Visión España y Mexico 1', @canal_id, 'https://1601580044.rsc.cdn77.org/live/_jcn_/amls:Italiatre/playlist.m3u8', 936, 1, NULL, 1, 1, 'María Visión España y Mexico#0'),
('María Visión España y Mexico 2', @canal_id, 'https://1601580044.rsc.cdn77.org/live/_jcn_/amlst:Mariavision/master.m3u8', 936, 1, NULL, 1, 1, 'María Visión España y Mexico#1');

-- ── Medi1 TV Marruecos [International / Int. África] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Medi1 TV Marruecos', 'https://graph.facebook.com/Medi1TV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Medi1 TV Marruecos 1', @canal_id, 'https://cdn.live.easybroadcast.io/abr_corp/83_medi1tv-arabic_g90v4ec/playlist_dvr.m3u8', NULL, 1, 'Medi1.TV', 1, 1, 'Medi1 TV Marruecos#0'),
('Medi1 TV Marruecos 2', @canal_id, 'https://cdn.live.easybroadcast.io/abr_corp/83_medi1tv-maghreb_jnbspmg/playlist_dvr.m3u8', NULL, 1, 'Medi1.TV', 1, 1, 'Medi1 TV Marruecos#1'),
('Medi1 TV Marruecos 3', @canal_id, 'https://cdn.live.easybroadcast.io/abr_corp/83_medi1tv-afrique_tm7tu45/playlist_dvr.m3u8', NULL, 1, 'Medi1.TV', 1, 1, 'Medi1 TV Marruecos#2');

-- ── Mexico Travel TV [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Mexico Travel TV', 'https://graph.facebook.com/MexicoTravelChannelTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Mexico Travel TV', @canal_id, 'https://5ca9af4645e15.streamlock.net/mexicotravel/videomexicotravel/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Mexico Travel TV#0');

-- ── Miami TV Fashion [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Miami TV Fashion', 'https://miamitv.com/images/669a96975172d_Miami%20TV%20Fashion.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Miami TV Fashion', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16284&live=1&avod=0&hls_marker=1&pod_duration=120&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&content_cat=IAB8&content_channel=cocinafamiliar&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&ssai_enabled=1&content_id=mirametv_live&min_ad_duration=6&max_ad_duration=120&app_domain=mirametv.live&ua=[%UA%]&device_type=[DEVICE_TYPE]&min_ad_duration=6&max_ad_duration=120&ip=[IP]', NULL, 1, NULL, 1, 1, 'Miami TV Fashion#0');

-- ── Miami TV Gold [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Miami TV Gold', 'https://miamitv.com/images/1718230773.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Miami TV Gold', @canal_id, 'https://8f4cbe9fa8d44aa3bfd4283527a9effd.mediatailor.us-east-2.amazonaws.com/v1/master/c3d3fd1c31fa281b88eab2cd253e2ca576b6628b/fast_gold/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Miami TV Gold#0');

-- ── Miami TV Jenny Live [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Miami TV Jenny Live', 'https://miamitv.com/images/1718228364.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Miami TV Jenny Live', @canal_id, 'https://bdc8100df748400cabc7e133824c8ceb.mediatailor.us-east-2.amazonaws.com/v1/master/c3d3fd1c31fa281b88eab2cd253e2ca576b6628b/fast-jennylive/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Miami TV Jenny Live#0');

-- ── Miami TV Latino [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Miami TV Latino', 'https://miamitv.com/images/1718228333.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Miami TV Latino', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16285&live=1&avod=0&hls_marker=1&pod_duration=120&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&content_cat=IAB8&content_channel=cocinafamiliar&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&ssai_enabled=1&content_id=mirametv_live&min_ad_duration=6&max_ad_duration=120&app_domain=mirametv.live&ua=[%25UA%25]&device_type=[DEVICE_TYPE]&min_ad_duration=6&max_ad_duration=120&ip=[IP]', NULL, 1, NULL, 1, 1, 'Miami TV Latino#0');

-- ── MiBit Almansa TV [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('MiBit Almansa TV', 'https://yt3.googleusercontent.com/fZuxmUyLoiA6cHfEOGiJvvOhUZO_7W5ZBA7Tjui60IUM3HVCvP-ffk5gE8qxGhdlyql9XbHk9A=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('MiBit Almansa TV', @canal_id, 'https://tv.mibit.es/hls/mibit.m3u8', 441, 1, NULL, 1, 1, 'MiBit Almansa TV#0');

-- ── Mijas 3.40 TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Mijas 3.40 TV', 'https://graph.facebook.com/Mijas340TV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Mijas 3.40 TV', @canal_id, 'https://streaming004.gestec-video.com/hls/MIJAS.m3u8', 441, 1, 'Mijas340TV.TV', 1, 1, 'Mijas 3.40 TV#0');

-- ── Molahits TV España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Molahits TV España', 'https://graph.facebook.com/molahitstv/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Molahits TV España', @canal_id, 'https://ventdelnord.tv:8080/mola/directe.m3u8', NULL, 1, NULL, 1, 1, 'Molahits TV España#0');

-- ── MoreThanSports TV [International / Deportivos Int.] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('MoreThanSports TV', 'https://graph.facebook.com/mtssportstv/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('MoreThanSports TV', @canal_id, 'https://mts1.iptv-playoutcenter.de/mts/mts-web/playlist.m3u8', NULL, 1, NULL, 1, 1, 'MoreThanSports TV#0');

-- ── Mírame TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Mírame TV', 'https://graph.facebook.com/mirametvcom/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Mírame TV', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=13696&live=1', 441, 1, 'MirameTV.TV', 1, 1, 'Mírame TV#0');

-- ── N1 Croacia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('N1 Croacia', 'https://graph.facebook.com/N1Hrvatska/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('N1 Croacia', @canal_id, 'https://best-str.umn.cdn.united.cloud/stream?stream=sp1400&sp=n1info&channel=n1hrv&u=n1info&p=n1Sh4redSecre7iNf0&player=m3u8', NULL, 1, NULL, 1, 1, 'N1 Croacia#0');

-- ── NDR Niedersachsen Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('NDR Niedersachsen Alemania', 'https://graph.facebook.com/ndrniedersachsen/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('NDR Niedersachsen Alemania', @canal_id, 'https://ndrint.akamaized.net/hls/live/2020766/ndr_int/master.m3u8', 723, 1, NULL, 1, 1, 'NDR Niedersachsen Alemania#0');

-- ── Negocios TV [Spain / Informativos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Negocios TV', 'https://pbs.twimg.com/profile_images/1321367703731523584/bNMmbetI_200x200.jpg', 2, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Negocios TV', @canal_id, 'https://negociostv-negociostv-samsunges.amagi.tv/hls/amagi_hls_data_negociost-negociostv-samsunges/CDN/playlist.m3u8', 441, 1, 'Negocios.TV', 1, 1, 'Negocios TV#0');

-- ── New Delhi TV 24x7 India [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('New Delhi TV 24x7 India', 'https://graph.facebook.com/ndtv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('New Delhi TV 24x7 India', @canal_id, 'https://ndtv24x7elemarchana.akamaized.net/hls/live/2003678/ndtv24x7/ndtv24x7master.m3u8', NULL, 1, NULL, 1, 1, 'New Delhi TV 24x7 India#0');

-- ── Newsmax TV USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Newsmax TV USA', 'https://graph.facebook.com/newsmax/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Newsmax TV USA 1', @canal_id, 'https://nmxlive.akamaized.net/hls/live/529965/Live_1/index.m3u8', 542, 1, NULL, 1, 1, 'Newsmax TV USA#0'),
('Newsmax TV USA 2', @canal_id, 'https://nmx1ota.akamaized.net/hls/live/2107010/Live_1/index.m3u8', 542, 1, NULL, 1, 1, 'Newsmax TV USA#1');

-- ── NHK World Japón [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('NHK World Japón', 'https://graph.facebook.com/nhkworld/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('NHK World Japón', @canal_id, 'https://media-tyo.hls.nhkworld.jp/hls/w/live/master.m3u8', NULL, 1, NULL, 1, 1, 'NHK World Japón#0');

-- ── Nippon News TV Japón [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Nippon News TV Japón', 'https://graph.facebook.com/ntvnews24/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Nippon News TV Japón', @canal_id, 'https://n24-cdn-live-x.ntv.co.jp/ch01/index.m3u8?', NULL, 1, NULL, 1, 1, 'Nippon News TV Japón#0');

-- ── Noroeste TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Noroeste TV', 'https://graph.facebook.com/noroestetvladesiempre/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Noroeste TV', @canal_id, 'https://stream.castr.com/5d1f649bed75c92e40481734/live_19364d50fbcd11ed91bd012c3488eabc/index.fmp4.m3u8', 441, 1, NULL, 1, 1, 'Noroeste TV#0');

-- ── NORTEvisión [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('NORTEvisión', 'https://graph.facebook.com/aljoamyvisual/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('NORTEvisión', @canal_id, 'http://amaru.dyndns.org:8870/0.m3u8', 441, 1, NULL, 1, 1, 'NORTEvisión#0');

-- ── Noticias RCN [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Noticias RCN', 'https://graph.facebook.com/NoticiasRCN/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Noticias RCN', @canal_id, 'https://epg.provider.plex.tv/library/parts/643054b1fc3be59477853717-67e6979de1de65be88aeb39f/?X-Plex-Token=zBXsxVNLkczFVyqfDaGK', NULL, 1, NULL, 1, 1, 'Noticias RCN#0');

-- ── NRG91 Grecia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('NRG91 Grecia', 'https://yt3.ggpht.com/KGxBhcmGT-UX_3Hhnfw7Gwnypyn4XzUQ3_OElJuNKllBcZmE58-z_FpozwfIxl9fA7z9RPnVBwE=s200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('NRG91 Grecia', @canal_id, 'http://tv.nrg91.gr:1935/onweb/live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'NRG91 Grecia#0');

-- ── Number1 FM Turquía [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Number1 FM Turquía', 'https://graph.facebook.com/Number1FM/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Number1 FM Turquía', @canal_id, 'https://b01c02nl.mediatriple.net/videoonlylive/mtkgeuihrlfwlive/broadcast_5c9e17cd59e8b.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Number1 FM Turquía#0');

-- ── Ocko Chequia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ocko Chequia', 'https://graph.facebook.com/tvocko/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ocko Chequia', @canal_id, 'https://ocko-live.ssl.cdn.cra.cz/channels/ocko/playlist/cze/live_hq.m3u8', NULL, 1, NULL, 1, 1, 'Ocko Chequia#0');

-- ── Oizmendi TB [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Oizmendi TB', 'https://graph.facebook.com/oizmenditelebista/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Oizmendi TB', @canal_id, 'https://zuzenean.oizmendi.eus/hls/z.m3u8', 441, 1, NULL, 1, 1, 'Oizmendi TB#0');

-- ── Oman TV [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Oman TV', 'https://graph.facebook.com/OmanTvGeneral/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Oman TV', @canal_id, 'https://partneta.cdn.mgmlcdn.com/omantv/smil:omantv.stream.smil/master.m3u8', NULL, 1, NULL, 1, 1, 'Oman TV#0');

-- ── Onda 15 TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Onda 15 TV', 'https://pbs.twimg.com/profile_images/452144347593465856/NWj5Y9hn_200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Onda 15 TV', @canal_id, 'https://cloudvideo.servers10.com:8081/8034/index.m3u8', 441, 1, NULL, 1, 1, 'Onda 15 TV#0');

-- ── Onda Algeciras TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Onda Algeciras TV', 'https://graph.facebook.com/ondaalgecirastv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Onda Algeciras TV', @canal_id, 'https://cloudtv.provideo.es/live/algecirastv-livestream.m3u8', 441, 1, 'OndaAlgeciras.TV', 1, 1, 'Onda Algeciras TV#0');

-- ── Onda Cádiz [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Onda Cádiz', 'https://graph.facebook.com/ondacadiz/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Onda Cádiz 1', @canal_id, 'https://liveingesta318.cdnmedia.tv/ondacadizlive/smil:ondacadiztv.smil/playlist.m3u8?DVR', 441, 1, 'OndaCadiz.TV', 1, 1, 'Onda Cádiz#0'),
('Onda Cádiz 2', @canal_id, 'https://ondacadiztv.es:30443/octv/24h/playlist.m3u8', 441, 1, 'OndaCadiz.TV', 1, 1, 'Onda Cádiz#1');

-- ── Onda Valencia [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Onda Valencia', 'https://graph.facebook.com/ondavalenciatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Onda Valencia', @canal_id, 'https://cloudvideo.servers10.com:8081/8116/index.m3u8', 441, 1, NULL, 1, 1, 'Onda Valencia#0');

-- ── On TV Portugal [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('On TV Portugal', 'https://graph.facebook.com/ONFM93.8/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('On TV Portugal', @canal_id, 'https://5ce9406b73c33.streamlock.net/ONFM/livestream/master.m3u8', NULL, 1, NULL, 1, 1, 'On TV Portugal#0');

-- ── Orgullo (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Orgullo (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Orgullo (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/16620298.m3u8', 441, 1, 'RTVE_PopUp2.TV', 1, 1, 'Orgullo (RTVE)#0');

-- ── Oromar TV Ecuador [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Oromar TV Ecuador', 'https://graph.facebook.com/oromartv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Oromar TV Ecuador', @canal_id, 'https://stream.oromar.tv/hls/oromartv_hi/index.m3u8', NULL, 1, NULL, 1, 1, 'Oromar TV Ecuador#0');

-- ── Parlamento de Andalucía [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Parlamento de Andalucía', 'https://graph.facebook.com/parlamentodeandalucia.es/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Parlamento de Andalucía 1', @canal_id, 'https://stream1.parlamentodeandalucia.es/realizacion1/realizacion1/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de Andalucía#0'),
('Parlamento de Andalucía 2', @canal_id, 'https://stream2.parlamentodeandalucia.es/realizacion2/realizacion2/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de Andalucía#1'),
('Parlamento de Andalucía 3', @canal_id, 'https://stream2.parlamentodeandalucia.es/realizacion3/realizacion3/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de Andalucía#2'),
('Parlamento de Andalucía 4', @canal_id, 'https://stream2.parlamentodeandalucia.es/realizacion4/realizacion4/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de Andalucía#3');

-- ── Parlamento de Galicia [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Parlamento de Galicia', 'https://graph.facebook.com/parlamentodegalicia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Parlamento de Galicia', @canal_id, 'https://pgalicia-live.akamaized.net/hls/live/2040697/pleno/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de Galicia#0');

-- ── Parlamento de La Rioja [Spain / La Rioja] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Parlamento de La Rioja', 'https://graph.facebook.com/ParlamentodeLaRioja/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Parlamento de La Rioja 1', @canal_id, 'https://media.parlamento-larioja.org/live/parlarioja/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de La Rioja#0'),
('Parlamento de La Rioja 2', @canal_id, 'https://media.parlamento-larioja.org/live/parlarioja_subtitulado/playlist.m3u8', 441, 1, NULL, 1, 1, 'Parlamento de La Rioja#1');

-- ── Parlamento de Navarra [Spain / C. Foral de Navarra] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Parlamento de Navarra', 'https://pbs.twimg.com/profile_images/1517046445030924289/r4OIw84T_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Parlamento de Navarra 1', @canal_id, 'https://broadcasting.parlamentodenavarra.es/live/canal1/playlist.m3u8?DVR', 441, 1, NULL, 1, 1, 'Parlamento de Navarra#0'),
('Parlamento de Navarra 2', @canal_id, 'https://broadcasting.parlamentodenavarra.es/live/canal2/playlist.m3u8?DVR', 441, 1, NULL, 1, 1, 'Parlamento de Navarra#1'),
('Parlamento de Navarra 3', @canal_id, 'https://broadcasting.parlamentodenavarra.es/live/canal3/playlist.m3u8?DVR', 441, 1, NULL, 1, 1, 'Parlamento de Navarra#2');

-- ── Pat Bolivia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Pat Bolivia', 'https://graph.facebook.com/patboliviahd/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Pat Bolivia', @canal_id, 'https://www.redpat.tv:8000/play/12/74929205.m3u8', NULL, 1, NULL, 1, 1, 'Pat Bolivia#0');

-- ── Penedès TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Penedès TV', 'https://graph.facebook.com/rtvvilafranca/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Penedès TV', @canal_id, 'https://liveingesta318.cdnmedia.tv/rtvvilafrancalive/smil:live.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_Penedes_TV.TV', 1, 1, 'Penedès TV#0');

-- ── Pequeradio TV [Spain / Infantiles] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Pequeradio TV', 'https://graph.facebook.com/Pequeradio/picture?width=200&height=200', 5, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Pequeradio TV', @canal_id, 'https://183.bozztv.com/ssh101/ssh101/pequeradiotv/playlist.m3u8', 441, 1, NULL, 1, 1, 'Pequeradio TV#0');

-- ── Piera TV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Piera TV', 'https://yt3.ggpht.com/Yo_LIch5OT5hTA24FMlshk7MtHpuUbVoOd8U2HJGw6el7-cCkAhH8_ISKmww17wHn37FCOF_rg=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Piera TV', @canal_id, 'https://5d776b1861da1.streamlock.net/piera/smil:piera.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Piera TV#0');

-- ── Popular TV Cantabria [Spain / Cantabria] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Popular TV Cantabria', 'https://graph.facebook.com/populartvcantabria/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Popular TV Cantabria', @canal_id, 'https://limited12.todostreaming.es/live/ptvcantabria-livestream.m3u8', 441, 1, 'PopularTV_S.TV', 1, 1, 'Popular TV Cantabria#0');

-- ── Popular TV Melilla [Spain / Melilla] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Popular TV Melilla', 'https://pbs.twimg.com/profile_images/61224728/populartvtwitter_200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Popular TV Melilla', @canal_id, 'https://5940924978228.streamlock.net/8009/ngrp:8009_all/playlist.m3u8', 441, 1, NULL, 1, 1, 'Popular TV Melilla#0');

-- ── Popular TV Murcia [Spain / R. de Murcia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Popular TV Murcia', 'https://pbs.twimg.com/profile_images/61224728/populartvtwitter_200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Popular TV Murcia', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12690&live=1', 441, 1, 'PopularMU.TV', 1, 1, 'Popular TV Murcia#0');

-- ── Portal Foxmix Chile [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Portal Foxmix Chile', 'https://yt3.ggpht.com/ytc/AAUvwnj90kC8kqjZ69oiVT718JUs9iedB5o1w9cKfApo=s200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Portal Foxmix Chile', @canal_id, 'https://panel.tvstream.cl:1936/8040/8040/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Portal Foxmix Chile#0');

-- ── Power TV Turquía [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Power TV Turquía', 'https://graph.facebook.com/powerapp/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Power TV Turquía', @canal_id, 'https://livetv.powerapp.com.tr/powerTV/powerhd.smil/playlists.m3u8', NULL, 1, NULL, 1, 1, 'Power TV Turquía#0');

-- ── PTV Córdoba [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('PTV Córdoba', 'https://graph.facebook.com/PTVCOR/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('PTV Córdoba', @canal_id, 'https://streamer.zapitv.com/PTV_CORDOBA/index.m3u8', 441, 1, NULL, 1, 1, 'PTV Córdoba#0');

-- ── PTV Granada [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('PTV Granada', 'https://graph.facebook.com/PTVGranada/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('PTV Granada', @canal_id, 'https://streamer.zapitv.com/PTV-granada/index.m3u8', 441, 1, 'PTV_Granada.TV', 1, 1, 'PTV Granada#0');

-- ── PTV Linares [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('PTV Linares', 'https://graph.facebook.com/tvlinares/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('PTV Linares', @canal_id, 'https://streamer.zapitv.com/ptv-linarez/index.m3u8', 441, 1, 'PTV_Linares.TV', 1, 1, 'PTV Linares#0');

-- ── PTV Málaga [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('PTV Málaga', 'https://graph.facebook.com/PTVMalaga/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('PTV Málaga', @canal_id, 'https://streamer.zapitv.com/PTV-malaga/index.m3u8', 441, 1, 'PTV_Malaga.TV', 1, 1, 'PTV Málaga#0');

-- ── PTV Sevilla [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('PTV Sevilla', 'https://graph.facebook.com/SevillaPTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('PTV Sevilla', @canal_id, 'https://streamer.zapitv.com/PTV_sevilla/index.m3u8', 441, 1, 'PTV_Sevilla.TV', 1, 1, 'PTV Sevilla#0');

-- ── Punt 3 Vall Uixó [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Punt 3 Vall Uixó', 'https://graph.facebook.com/Punt3Television/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Punt 3 Vall Uixó', @canal_id, 'https://bit.controlstreams.com:5443/LiveApp/streams/punt3.m3u8', 441, 1, 'Punt3.TV', 1, 1, 'Punt 3 Vall Uixó#0');

-- ── QMusic Países Bajos [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('QMusic Países Bajos', 'https://graph.facebook.com/QmusicNL/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('QMusic Países Bajos', @canal_id, 'https://stream.qmusic.nl/qmusic/videohls.m3u8', NULL, 1, NULL, 1, 1, 'QMusic Países Bajos#0');

-- ── Quiero TV Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Quiero TV Mexico', 'https://graph.facebook.com/quierotvGDL/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Quiero TV Mexico', @canal_id, 'https://stream.ontvmx.com/ontv/ghxTYEQmKkB2UJyVuW/playlist.m3u8', 936, 1, NULL, 1, 1, 'Quiero TV Mexico#0');

-- ── Radio 3 [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio 3', 'https://graph.facebook.com/radio3/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio 3', @canal_id, 'https://ztnr.rtve.es/ztnr/6982918.m3u8', 441, 1, 'RNE_Radio3.TV', 1, 1, 'Radio 3#0');

-- ── Radio 5 [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio 5', 'https://pbs.twimg.com/profile_images/1405097207339028480/H7nP_7Ti_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio 5', @canal_id, 'https://ztnr.rtve.es/ztnr/6982917.m3u8', 441, 1, 'RNE_Radio5.TV', 1, 1, 'Radio 5#0');

-- ── Radio Buñol TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio Buñol TV', 'https://graph.facebook.com/radiobunyol/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio Buñol TV', @canal_id, 'https://radiotvbunollive.flumotion.cloud/radiotvbunollive/smil:channel1.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Radio Buñol TV#0');

-- ── Radio Calima TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio Calima TV', 'https://graph.facebook.com/calimafm/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio Calima TV', @canal_id, 'https://nrvideo1.newradio.it:443/calimafm/calimafm/playlist.m3u8', 441, 1, NULL, 1, 1, 'Radio Calima TV#0');

-- ── Radio Capital Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio Capital Italia', 'https://graph.facebook.com/RadioCapitalfm/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio Capital Italia', @canal_id, 'https://4c4b867c89244861ac216426883d1ad0.msvdn.net/live/S35394734/Z6U2wGoDYANk/playlist.m3u8', 408, 1, NULL, 1, 1, 'Radio Capital Italia#0');

-- ── Radio Italia TV [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio Italia TV', 'https://graph.facebook.com/radioitalia/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio Italia TV', @canal_id, 'https://radioitaliatv.akamaized.net/hls/live/2093117/RadioitaliaTV/master.m3u8', NULL, 1, NULL, 1, 1, 'Radio Italia TV#0');

-- ── Radio Nacional [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Radio Nacional', 'https://graph.facebook.com/radionacionalrne/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Radio Nacional', @canal_id, 'https://ztnr.rtve.es/ztnr/6982891.m3u8', 441, 1, 'RNE.TV', 1, 1, 'Radio Nacional#0');

-- ── RadioU TV USA [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RadioU TV USA', 'https://graph.facebook.com/RadioU/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RadioU TV USA', @canal_id, 'https://1826200335.rsc.cdn77.org/1826200335/index.m3u8', 542, 1, NULL, 1, 1, 'RadioU TV USA#0');

-- ── RASD TV [International / Int. África] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RASD TV', 'https://graph.facebook.com/televisionsaharaui/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RASD TV', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12830&live=1', NULL, 1, NULL, 1, 1, 'RASD TV#0');

-- ── RDS Social TV Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RDS Social TV Italia', 'https://graph.facebook.com/rds.grandisuccessi/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RDS Social TV Italia', @canal_id, 'https://stream.rdstv.radio/index.m3u8', 408, 1, NULL, 1, 1, 'RDS Social TV Italia#0');

-- ── Real Madrid TV [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Real Madrid TV', 'https://graph.facebook.com/RealMadrid/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Real Madrid TV 1', @canal_id, 'https://rmtv.akamaized.net/hls/live/2043153/rmtv-es-web/master.m3u8', 441, 1, 'RMTV.TV', 1, 1, 'Real Madrid TV#0'),
('Real Madrid TV 2', @canal_id, 'https://rmtv.akamaized.net/hls/live/2043154/rmtv-en-web/master.m3u8', 441, 1, 'RMTV.TV', 1, 1, 'Real Madrid TV#1');

-- ── Red Bull TV [International / Deportivos Int.] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Red Bull TV', 'https://pbs.twimg.com/profile_images/626481857161375748/OeXi9avz_200x200.png', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Red Bull TV', @canal_id, 'https://rbmn-live.akamaized.net/hls/live/590964/BoRB-AT/master.m3u8', NULL, 1, NULL, 1, 1, 'Red Bull TV#0');

-- ── Redevida Brasil [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Redevida Brasil', 'https://graph.facebook.com/redevida/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Redevida Brasil', @canal_id, 'https://d12e4o88jd8gex.cloudfront.net/out/v1/cea3de0b76ac4e82ab8ee0fd3f17ce12/index.m3u8', NULL, 1, NULL, 1, 1, 'Redevida Brasil#0');

-- ── Republic World TV India [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Republic World TV India', 'https://graph.facebook.com/RepublicWorld/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Republic World TV India', @canal_id, 'https://vg-republictvlive.akamaized.net/v1/master/611d79b11b77e2f571934fd80ca1413453772ac7/vglive-sk-366023/main.m3u8', NULL, 1, NULL, 1, 1, 'Republic World TV India#0');

-- ── Retro Music TV Chequia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Retro Music TV Chequia', 'https://graph.facebook.com/retromusic/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Retro Music TV Chequia', @canal_id, 'http://stream.mediawork.cz/retrotv/retrotvHQ1/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Retro Music TV Chequia#0');

-- ── Retro Plus TV Chile [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Retro Plus TV Chile', 'https://graph.facebook.com/retroplustv/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Retro Plus TV Chile', @canal_id, 'https://scl.edge.grupoz.cl/retroplustvuno/live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Retro Plus TV Chile#0');

-- ── Ribera TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ribera TV', 'https://graph.facebook.com/grup.televisio/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ribera TV', @canal_id, 'https://common01.todostreaming.es/live/ribera-livestream.m3u8', 441, 1, NULL, 1, 1, 'Ribera TV#0');

-- ── RNE para todos [Spain / Generalistas] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RNE para todos', 'https://graph.facebook.com/radionacionalrne/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RNE para todos 1', @canal_id, 'https://ztnr.rtve.es/ztnr/6688753.m3u8', 441, 1, 'RNE.TV', 1, 1, 'RNE para todos#0'),
('RNE para todos 2', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/rne/rne_para_todos_main.m3u8', 441, 1, 'RNE.TV', 1, 1, 'RNE para todos#1');

-- ── RTCG SAT Montenegro [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTCG SAT Montenegro', 'https://graph.facebook.com/RTCG.me/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTCG SAT Montenegro', @canal_id, 'https://rtcg-live-open.morescreens.com/RTCG_1_004/playlist.m3u8', NULL, 1, NULL, 1, 1, 'RTCG SAT Montenegro#0');

-- ── RTHK 31 32 Hong Kong [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTHK 31 32 Hong Kong', 'https://graph.facebook.com/RTHK.HK/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTHK 31 32 Hong Kong 1', @canal_id, 'https://www.rthk.hk/feeds/dtt/rthktv31_https.m3u8', NULL, 1, NULL, 1, 1, 'RTHK 31 32 Hong Kong#0'),
('RTHK 31 32 Hong Kong 2', @canal_id, 'https://www.rthk.hk/feeds/dtt/rthktv32_https.m3u8', NULL, 1, NULL, 1, 1, 'RTHK 31 32 Hong Kong#1');

-- ── RTL 102.5 Best Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Best Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Best Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S76960628/OEPHRUIctA0M/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Best Italia#0');

-- ── RTL 102.5 Bro&Sis Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Bro&Sis Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Bro&Sis Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S75007890/MUGHuxc9dQ3b/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Bro&Sis Italia#0');

-- ── RTL 102.5 Caliente Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Caliente Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Caliente Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S8448465/zTYa1Z5Op9ue/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Caliente Italia#0');

-- ── RTL 102.5 Disco Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Disco Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Disco Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S51100361/0Fb4R3k82b5Z/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Disco Italia#0');

-- ── RTL 102.5 Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S97044836/tbbP8T1ZRPBL/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Italia#0');

-- ── RTL 102.5 Napulè Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Napulè Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Napulè Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S27134503/0f9AhuwKlhnZ/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Napulè Italia#0');

-- ── RTL 102.5 Traffic Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL 102.5 Traffic Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL 102.5 Traffic Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S38122967/2lyQRIAAGgRR/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL 102.5 Traffic Italia#0');

-- ── RTL Radio Freccia Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL Radio Freccia Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL Radio Freccia Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S3160845/0tuSetc8UFkF/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL Radio Freccia Italia#0');

-- ── RTL Zeta Italia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTL Zeta Italia', 'https://graph.facebook.com/RTL102.5/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTL Zeta Italia', @canal_id, 'https://dd782ed59e2a4e86aabf6fc508674b59.msvdn.net/live/S9346184/XEx1LqlYbNic/playlist_video.m3u8', 408, 1, NULL, 1, 1, 'RTL Zeta Italia#0');

-- ── RTV Cardedeu [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTV Cardedeu', 'https://graph.facebook.com/TelevisioCardedeu/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTV Cardedeu', @canal_id, 'https://liveingesta318.cdnmedia.tv/tvcardedeulive/smil:rtmp01.smil/playlist.m3u8?DVR', 441, 1, NULL, 1, 1, 'RTV Cardedeu#0');

-- ── RTV Diocesana Toledo España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTV Diocesana Toledo España', 'https://pbs.twimg.com/profile_images/1730156030795939840/NtRBSxdr_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTV Diocesana Toledo España', @canal_id, 'https://live.emitstream.com/hls/5i3pxfuz4az356yu22ij/master.m3u8', NULL, 1, NULL, 1, 1, 'RTV Diocesana Toledo España#0');

-- ── RTV El Vendrell [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTV El Vendrell', 'https://graph.facebook.com/rtvelvendrell/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTV El Vendrell', @canal_id, 'https://liveingesta318.cdnmedia.tv/tvvendrelllive/smil:directe.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'RTV El Vendrell#0');

-- ── RTV Mogán [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTV Mogán', 'https://graph.facebook.com/radiotelevisionmogan/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTV Mogán', @canal_id, 'https://cloudvideo.servers10.com:8081/8028/index.m3u8', 441, 1, NULL, 1, 1, 'RTV Mogán#0');

-- ── RTV Vida España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RTV Vida España', 'https://pbs.twimg.com/profile_images/1359486927406321664/WZXLfd2h_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RTV Vida España', @canal_id, 'https://vidartv2.todostreaming.es/live/radiovida-emisiontvhd.m3u8', NULL, 1, NULL, 1, 1, 'RTV Vida España#0');

-- ── RÚV Islandia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('RÚV Islandia', 'https://graph.facebook.com/RUVohf/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('RÚV Islandia 1', @canal_id, 'https://ruv-web-live.akamaized.net/streymi/ruverl/ruverl.m3u8', NULL, 1, NULL, 1, 1, 'RÚV Islandia#0'),
('RÚV Islandia 2', @canal_id, 'https://ruvlive.akamaized.net/out/v1/2ff7673de40f419fa5164498fae89089/index.m3u8', NULL, 1, NULL, 1, 1, 'RÚV Islandia#1');

-- ── Ràdio 4 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Ràdio 4', 'https://graph.facebook.com/Radio4RNE/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Ràdio 4', @canal_id, 'https://ztnr.rtve.es/ztnr/6982935.m3u8', 441, 1, 'RNE_Radio4.TV', 1, 1, 'Ràdio 4#0');

-- ── Saja Nansa TV [Spain / Cantabria] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Saja Nansa TV', 'https://graph.facebook.com/ondaoccidental/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Saja Nansa TV', @canal_id, 'https://streamlov.alsolnet.com/sajanansatv/live/playlist.m3u8', 441, 1, NULL, 1, 1, 'Saja Nansa TV#0');

-- ── Sal TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Sal TV', 'https://graph.facebook.com/SalTelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Sal TV', @canal_id, 'https://play.agenciastreaming.com:8081/saltv/index.m3u8', 441, 1, NULL, 1, 1, 'Sal TV#0');

-- ── San Marino RTV [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('San Marino RTV', 'https://graph.facebook.com/SanMarinoRTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('San Marino RTV 1', @canal_id, 'https://d2hrvno5bw6tg2.cloudfront.net/smrtv-ch01/_definst_/smil:ch-01.smil/master.m3u8', NULL, 1, NULL, 1, 1, 'San Marino RTV#0'),
('San Marino RTV 2', @canal_id, 'https://d2hrvno5bw6tg2.cloudfront.net/smrtv-ch02/_definst_/smil:ch-02.smil/master.m3u8', NULL, 1, NULL, 1, 1, 'San Marino RTV#1');

-- ── Semos asina (Canal Extremadura) [Spain / Extremadura] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Semos asina (Canal Extremadura)', 'https://graph.facebook.com/CanalExtremadura/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Semos asina (Canal Extremadura)', @canal_id, 'https://cdn-canalextremadura.watchity.net/fast3/master.m3u8', 441, 1, NULL, 1, 1, 'Semos asina (Canal Extremadura)#0');

-- ── Senado [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Senado', 'https://pbs.twimg.com/profile_images/2015722457508810752/NpsKmNCK_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Senado 1', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006551/punto1/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#0'),
('Senado 2', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006591/punto2/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#1'),
('Senado 3', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006592/punto3/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#2'),
('Senado 4', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006593/punto4/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#3'),
('Senado 5', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006594/punto5/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#4'),
('Senado 6', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006595/punto6/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#5'),
('Senado 7', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006589/punto7/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#6'),
('Senado 8', @canal_id, 'https://senadolive.akamaized.net/hls/live/2006590/punto8/master.m3u8', 441, 1, NULL, 1, 1, 'Senado#7');

-- ── ServusTV WetterPanorama [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('ServusTV WetterPanorama', 'https://graph.facebook.com/ServusTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('ServusTV WetterPanorama', @canal_id, 'https://rbmn-live.akamaized.net/hls/live/665268/Wetterpanorama/master.m3u8', NULL, 1, NULL, 1, 1, 'ServusTV WetterPanorama#0');

-- ── Señal Colombia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Señal Colombia', 'https://graph.facebook.com/senalcolombiapaginaoficial/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Señal Colombia', @canal_id, 'https://streaming.rtvc.gov.co/TV_Senal_Colombia_live/smil:live.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Señal Colombia#0');

-- ── Sky Folk Macedonia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Sky Folk Macedonia', 'https://graph.facebook.com/skyfolk.mk/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Sky Folk Macedonia', @canal_id, 'https://eu.live.skyfolk.mk/live.m3u8', NULL, 1, NULL, 1, 1, 'Sky Folk Macedonia#0');

-- ── Sky News Reino Unido [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Sky News Reino Unido', 'https://graph.facebook.com/skynews/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Sky News Reino Unido', @canal_id, 'https://linear417-gb-hls1-prd-ak.cdn.skycdp.com/100e/Content/HLS_001_1080_30/Live/channel(skynews)/index_1080-30.m3u8', NULL, 1, NULL, 1, 1, 'Sky News Reino Unido#0');

-- ── Solidaria TV España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Solidaria TV España', 'https://graph.facebook.com/solidariatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Solidaria TV España', @canal_id, 'https://canadaremar2.todostreaming.es/live/solidariatv-webhd.m3u8', NULL, 1, NULL, 1, 1, 'Solidaria TV España#0');

-- ── Sol Música España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Sol Música España', 'https://graph.facebook.com/solmusica/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Sol Música España', @canal_id, 'https://d2glyu450vvghm.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-21u4g5cjglv02/sm.m3u8', NULL, 1, 'SolMusica.TV', 1, 1, 'Sol Música España#0');

-- ── Somos Cine (RTVE) [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Somos Cine (RTVE)', 'https://graph.facebook.com/rtveplay/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Somos Cine (RTVE)', @canal_id, 'https://ztnr.rtve.es/ztnr/6909845.m3u8', 441, 1, 'RTVE_SomosCine.TV', 1, 1, 'Somos Cine (RTVE)#0');

-- ── Spektra TV España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Spektra TV España', 'https://graph.facebook.com/spektramusictv/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Spektra TV España', @canal_id, 'https://cloudvideo.servers10.com:8081/8026/tracks-v1a1/index.m3u8', NULL, 1, NULL, 1, 1, 'Spektra TV España#0');

-- ── Spirit TV USA [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Spirit TV USA', 'https://graph.facebook.com/MySpirittv/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Spirit TV USA', @canal_id, 'https://cdnlive.myspirit.tv/LS-ATL-43240-2/index.m3u8', 542, 1, NULL, 1, 1, 'Spirit TV USA#0');

-- ── Sport Italia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Sport Italia', 'https://graph.facebook.com/sportitaliatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Sport Italia 1', @canal_id, 'https://origin-001.streamup.eu/sportitalia/sihd_abr2/playlist.m3u8', 408, 1, NULL, 1, 1, 'Sport Italia#0'),
('Sport Italia 2', @canal_id, 'https://origin-001.streamup.eu/sportitalia/sisolocalcio_abr/playlist.m3u8', 408, 1, NULL, 1, 1, 'Sport Italia#1'),
('Sport Italia 3', @canal_id, 'https://origin-001.streamup.eu/sportitalia/silive24_abr/playlist.m3u8', 408, 1, NULL, 1, 1, 'Sport Italia#2');

-- ── SR Saarland Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('SR Saarland Alemania', 'https://graph.facebook.com/SRinfo.sr/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('SR Saarland Alemania', @canal_id, 'https://srfs.akamaized.net/hls/live/689649/srfsgeo/index.m3u8', 723, 1, NULL, 1, 1, 'SR Saarland Alemania#0');

-- ── Stadium USA [International / Deportivos Int.] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Stadium USA', 'https://pbs.twimg.com/profile_images/1912970794524610560/M1vEMVlm_200x200.jpg', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Stadium USA', @canal_id, 'https://2d006483e2aa43fe812f7b464cb2916d.mediatailor.us-east-1.amazonaws.com/v1/master/44f73ba4d03e9607dcd9bebdcb8494d86964f1d8/Samsung_Stadium/playlist.m3u8', 542, 1, NULL, 1, 1, 'Stadium USA#0');

-- ── Star Chequia [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Star Chequia', 'https://graph.facebook.com/ockostar/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Star Chequia', @canal_id, 'https://ocko-live.ssl.cdn.cra.cz/channels/ocko_gold/playlist/cze/live_hq.m3u8', NULL, 1, NULL, 1, 1, 'Star Chequia#0');

-- ── Star TVE Europa [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Star TVE Europa', 'https://graph.facebook.com/STARTVEInternacional/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Star TVE Europa', @canal_id, 'https://rtvelivestream-rtveplayplus.rtve.es/rtvesec/int/star_main_dvr_720.m3u8', NULL, 1, 'TVE_STAR.TV', 1, 1, 'Star TVE Europa#0');

-- ── STZ Telebista [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('STZ Telebista', 'https://graph.facebook.com/StzGrupo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('STZ Telebista', @canal_id, 'https://cloudvideo.servers10.com:8081/stztelebista/index.m3u8', 441, 1, NULL, 1, 1, 'STZ Telebista#0');

-- ── SX3 [Spain / Infantiles] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('SX3', 'https://graph.facebook.com/SomSX3/picture?width=200&height=200', 5, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('SX3 1', @canal_id, 'https://directes-tv-cat.3catdirectes.cat/live-content/super3-hls/master.m3u8', 441, 1, 'SX3.TV', 1, 1, 'SX3#0'),
('SX3 2', @canal_id, 'https://directes-tv-es.3catdirectes.cat/live-content/super3-hls/master.m3u8', 441, 1, 'SX3.TV', 1, 1, 'SX3#1');

-- ── TAC 12 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TAC 12', 'https://graph.facebook.com/tacdotze/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TAC 12', @canal_id, 'https://ingest1-video.streaming-pro.com/tac12_ABR/stream/playlist.m3u8', 441, 1, 'Xarxa_TAC12.TV', 1, 1, 'TAC 12#0');

-- ── Tagesschau24 Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tagesschau24 Alemania', 'https://graph.facebook.com/tagesschau/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tagesschau24 Alemania', @canal_id, 'https://tagesschau.akamaized.net/hls/live/2020115/tagesschau/tagesschau_1/master.m3u8', 723, 1, NULL, 1, 1, 'Tagesschau24 Alemania#0');

-- ── Tastemade [International / Int. Otros] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tastemade', 'https://graph.facebook.com/TastemadeEs/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tastemade 1', @canal_id, 'https://cdn-uw2-prod.tsv2.amagi.tv/linear/amg00047-tastemade-tastemadees16international24i-ono/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Tastemade#0'),
('Tastemade 2', @canal_id, 'https://cdn-uw2-prod.tsv2.amagi.tv/linear/amg00047-tastemade-tastemadeinternationalenglish24i-ono/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Tastemade#1');

-- ── TBN España [International / Religiosos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TBN España', 'https://graph.facebook.com/TBNEspana/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TBN España', @canal_id, 'https://edge.xn--tbnespaa-j3a.es/LiveApp/streams/tbnlive.m3u8', NULL, 1, NULL, 1, 1, 'TBN España#0');

-- ── TEF [Spain / Illes Balears] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TEF', 'https://graph.facebook.com/TEFTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TEF', @canal_id, 'https://tef.servertv.online:3268/live/teflive.m3u8', 441, 1, NULL, 1, 1, 'TEF#0');

-- ── Tele 7 [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tele 7', 'https://graph.facebook.com/Tele7Radio7/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tele 7', @canal_id, 'https://ingest2-video.streaming-pro.com/tele7_ABR/stream/playlist.m3u8', 441, 1, NULL, 1, 1, 'Tele 7#0');

-- ── TELE 10 Nayarit Mexico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TELE 10 Nayarit Mexico', 'https://graph.facebook.com/Tele10Nayarit/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TELE 10 Nayarit Mexico', @canal_id, 'https://live.iplanay.gob.mx/hls/nayarittv.m3u8', 936, 1, NULL, 1, 1, 'TELE 10 Nayarit Mexico#0');

-- ── Teleantioquia Colombia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Teleantioquia Colombia', 'https://graph.facebook.com/CanalTeleantioquia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Teleantioquia Colombia', @canal_id, 'https://liveingesta118.cdnmedia.tv/teleantioquialive/smil:dvrlive.smil/playlist_DVR.m3u8', NULL, 1, NULL, 1, 1, 'Teleantioquia Colombia#0');

-- ── TeleBilbao [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleBilbao', 'https://graph.facebook.com/312994995454199/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleBilbao', @canal_id, 'https://player.telebilbao.es/hls/web-public/live.m3u8', 441, 1, NULL, 1, 1, 'TeleBilbao#0');

-- ── Teledeporte [Spain / Deportivos] (reutiliza canal #976 "Teledeporte ES") ──
SET @canal_id = 976;
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Teledeporte 1', @canal_id, 'https://rtvelivestream.rtve.es/rtvesec/tdp/tdp_main.m3u8', 441, 1, 'TDP.TV', 1, 1, 'Teledeporte#0'),
('Teledeporte 2', @canal_id, 'https://stream.ads.ottera.tv/playlist.m3u8?network_id=15601', 441, 1, 'TDP.TV', 1, 1, 'Teledeporte#1');

-- ── TeleDiario Costa Rica [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleDiario Costa Rica', 'https://graph.facebook.com/MultimediosCR/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleDiario Costa Rica', @canal_id, 'https://mdstrm.com/live-stream-playlist/5a7b1e63a8da282c34d65445.m3u8', NULL, 1, NULL, 1, 1, 'TeleDiario Costa Rica#0');

-- ── Teledifusão de Macau [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Teledifusão de Macau', 'https://graph.facebook.com/Canal.Macau/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Teledifusão de Macau 1', @canal_id, 'https://globallive.tdm.com.mo/ch2/ch2.live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Teledifusão de Macau#0'),
('Teledifusão de Macau 2', @canal_id, 'https://globallive.tdm.com.mo/ch1/ch1.live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Teledifusão de Macau#1'),
('Teledifusão de Macau 3', @canal_id, 'https://globallive.tdm.com.mo/ch3/ch3.live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Teledifusão de Macau#2');

-- ── TeleElx [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleElx', 'https://graph.facebook.com/teleelx/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleElx', @canal_id, 'https://tvdirecto.teleelx.es/stream/teleelx.m3u8', 441, 1, 'TeleElx.TV', 1, 1, 'TeleElx#0');

-- ── Teleganés [Spain / C. de Madrid] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Teleganés', 'https://graph.facebook.com/1423419417957760/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Teleganés', @canal_id, 'https://live.emitstream.com/hls/5z6oj7ziwxzfnj78vg2m/master.m3u8', 441, 1, NULL, 1, 1, 'Teleganés#0');

-- ── TeleGranada [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleGranada', 'https://graph.facebook.com/Telegranada/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleGranada', @canal_id, 'https://telegranada.es/hls/stream.m3u8', 441, 1, NULL, 1, 1, 'TeleGranada#0');

-- ── Telemiño [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Telemiño', 'https://graph.facebook.com/teleminho/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Telemiño', @canal_id, 'https://laregionlive.flumotion.cloud/laregionlive/smil:channel1.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'Telemiño#0');

-- ── Telemotril [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Telemotril', 'https://graph.facebook.com/telemotriltv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Telemotril', @canal_id, 'https://5940924978228.streamlock.net/8431/8431/playlist.m3u8', 441, 1, 'Telemotril.TV', 1, 1, 'Telemotril#0');

-- ── Teleonuba [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Teleonuba', 'https://graph.facebook.com/Teleonuba/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Teleonuba', @canal_id, 'https://5f71743aa95e4.streamlock.net:1936/Teleonuba/endirecto/playlist.m3u8', 441, 1, 'Teleonuba.TV', 1, 1, 'Teleonuba#0');

-- ── Telepacifico Colombia [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Telepacifico Colombia', 'https://graph.facebook.com/TelepacificoTV/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Telepacifico Colombia 1', @canal_id, 'https://live-edge-eu-1.cdn.enetres.net/6E5C615AA5FF4123ACAF0DAB57B7B8DC021/live-telepacifico/index.m3u8', NULL, 1, NULL, 1, 1, 'Telepacifico Colombia#0'),
('Telepacifico Colombia 2', @canal_id, 'https://play.cdn.enetres.net/6E5C615AA5FF4123ACAF0DAB57B7B8DC022/023/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Telepacifico Colombia#1');

-- ── TeleRibera [Spain / C. Foral de Navarra] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleRibera', 'https://pbs.twimg.com/profile_images/780539549239902208/g2MfVVuY_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleRibera', @canal_id, 'https://video3.lhdserver.es/teleribera/live.m3u8', 441, 1, NULL, 1, 1, 'TeleRibera#0');

-- ── Tele Safor [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tele Safor', 'https://yt3.ggpht.com/ytc/APkrFKZ5UffEAeHVZWc1fbQsPu4VNureSfNMwlMoRmgH=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tele Safor', @canal_id, 'https://video.telesafor.com/hls/video.m3u8', 441, 1, NULL, 1, 1, 'Tele Safor#0');

-- ── TeleSUR Venezuela [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleSUR Venezuela', 'https://graph.facebook.com/teleSUR/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleSUR Venezuela', @canal_id, 'https://mblesmain01.telesur.ultrabase.net/mbliveMain/hd/playlist.m3u8', NULL, 1, 'TeleSUR.TV', 1, 1, 'TeleSUR Venezuela#0');

-- ── TeleToledo [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleToledo', 'https://pbs.twimg.com/profile_images/1307981912586301441/LloEFyxw_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleToledo', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12688&live=1', 441, 1, NULL, 1, 1, 'TeleToledo#0');

-- ── TeleVigo [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TeleVigo', 'https://graph.facebook.com/televigo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TeleVigo', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16113&live=1&avod=0&hls_marker=1&position=preroll&pod_duration=120&app_bundle=com.streamingconnect.viva&app_domain=mirametv.live&app_category=linear_tv&ssai_enabled=1&content_cat=IAB1&content_channel=televigo&content_genre=tv_broadcaster&content_network=streamingconnect&content_rating=TV-G&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&min_ad_duration=6&max_ad_duration=120&content_id=[CONTENT_ID]', 441, 1, 'TeleVigo.TV', 1, 1, 'TeleVigo#0');

-- ── Televisión Alhaurín [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Televisión Alhaurín', 'https://graph.facebook.com/Rtvalhaurin/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Televisión Alhaurín', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=13354&live=1', 441, 1, NULL, 1, 1, 'Televisión Alhaurín#0');

-- ── Tenerife Plus TV [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tenerife Plus TV', 'https://graph.facebook.com/tenerifeplustv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tenerife Plus TV', @canal_id, 'https://k20.usastreams.com:8081/tenerifeplus/index.m3u8', 441, 1, NULL, 1, 1, 'Tenerife Plus TV#0');

-- ── Tevecan 9 [Spain / Cantabria] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tevecan 9', 'https://static.wixstatic.com/media/4d3432_610170cea6c747a986bbea4137f5c15f~mv2.png/v1/fill/w_200,h_200,al_c,q_85,usm_0.66_1.00_0.01,enc_auto/mosca%20logo%209_transparente.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tevecan 9', @canal_id, 'https://streamlov.alsolnet.com/jarbhouse/live/playlist.m3u8', 441, 1, NULL, 1, 1, 'Tevecan 9#0');

-- ── TG7 [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TG7', 'https://graph.facebook.com/TG7tv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TG7', @canal_id, 'https://flu-01.hucame.es/TG7/index.fmp4.m3u8', 441, 1, NULL, 1, 1, 'TG7#0');

-- ── Top Barça [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Top Barça', 'https://images-0.rakuten.tv/storage/global-live-channel/translation/artwork/ff403089-1c81-41d3-bb18-b817b1f01721-width200-quality90.jpeg', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Top Barça 1', @canal_id, 'https://amg17560-fcb-amg17560c2-samsung-es-9803.playouts.now.amagi.tv/ts-eu-w1-n2/playlist/amg17560-fcbarcelona-topbarcaspanish-samsunges/playlist.m3u8', 441, 1, 'Top_Barça.TV', 1, 1, 'Top Barça#0'),
('Top Barça 2', @canal_id, 'https://amg17560-fcb-amg17560c3-lg-es-11383.playouts.now.amagi.tv/ts-eu-w1-n2/playlist/amg17560-fcbarcelona-topbarcacatala-lges/playlist.m3u8', 441, 1, 'Top_Barça.TV', 1, 1, 'Top Barça#1');

-- ── Toros (Canal Extremadura) [Spain / Extremadura] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Toros (Canal Extremadura)', 'https://graph.facebook.com/CanalExtremadura/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Toros (Canal Extremadura)', @canal_id, 'https://cdn-canalextremadura.watchity.net/fast1/master.m3u8', 441, 1, NULL, 1, 1, 'Toros (Canal Extremadura)#0');

-- ── Torremolinos TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Torremolinos TV', 'https://graph.facebook.com/torremolinostv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Torremolinos TV', @canal_id, 'https://cdnlivevlc.codev8.net/canaltorremolinoslive/smil:channel1.smil/playlist.m3u8', 441, 1, 'Torrevision.TV', 1, 1, 'Torremolinos TV#0');

-- ── TRECE [Spain / Generalistas] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TRECE', 'https://graph.facebook.com/TRECEtves/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TRECE', @canal_id, 'https://play.cdn.enetres.net/091DB7AFBD77442B9BA2F141DCC182F5021/021/playlist.m3u8', 441, 1, '13.TV', 1, 1, 'TRECE#0');

-- ── Tropical Moon TV [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tropical Moon TV', 'https://graph.facebook.com/tropicalmoonfm/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tropical Moon TV 1', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16185&live=1&avod=0&hls_marker=1&pod_duration=120&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&content_channel=salsatv&content_cat=IAB14&content_genre=tv_broadcaster&content_id=mirametv_live&content_network=streamingconnect&content_rating=TV-G&ssai_enabled=1&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&min_ad_duration=6&max_ad_duration=120&app_domain=mirametv.live', NULL, 1, NULL, 1, 1, 'Tropical Moon TV#0'),
('Tropical Moon TV 2', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16184&live=1&avod=0&hls_marker=1&pod_duration=120&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&content_channel=cumbiatv&content_cat=IAB14&content_genre=tv_broadcaster&content_id=mirametv_live&content_network=streamingconnect&content_rating=TV-G&ssai_enabled=1&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&ifa_type=[IFA_TYPE]&min_ad_duration=6&max_ad_duration=120&app_domain=mirametv.live', NULL, 1, NULL, 1, 1, 'Tropical Moon TV#1');

-- ── TRT World Turquía [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TRT World Turquía', 'https://graph.facebook.com/trtworld/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TRT World Turquía 1', @canal_id, 'https://tv-trtworld.medya.trt.com.tr/master.m3u8', NULL, 1, NULL, 1, 1, 'TRT World Turquía#0'),
('TRT World Turquía 2', @canal_id, 'https://tv-trthaber.medya.trt.com.tr/master.m3u8', NULL, 1, NULL, 1, 1, 'TRT World Turquía#1');

-- ── Tuya La Janda TV [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Tuya La Janda TV', 'https://graph.facebook.com/tuyalajandatv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Tuya La Janda TV', @canal_id, 'https://nimble.tuyapro.es/app/tv/playlist.m3u8', 441, 1, 'TuyaLaJanda.TV', 1, 1, 'Tuya La Janda TV#0');

-- ── TV3 [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV3', 'https://pbs.twimg.com/profile_images/1269286508625891328/rVes9BS__200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV3', @canal_id, 'https://directes3-tv-cat.3catdirectes.cat/live-content/tv3-hls/master.m3u8', 441, 1, 'TV3.TV', 1, 1, 'TV3#0');

-- ── TV3.CAT [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV3.CAT', 'https://pbs.twimg.com/profile_images/1269286508625891328/rVes9BS__200x200.png', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV3.CAT', @canal_id, 'https://directes3-tv-int.3catdirectes.cat/live-content/tvi-hls/master.m3u8', NULL, 1, 'TVC.TV', 1, 1, 'TV3.CAT#0');

-- ── TV 4 La Vall [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV 4 La Vall', 'https://graph.facebook.com/TV4LaVall/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV 4 La Vall', @canal_id, 'https://valldeuxo.gestec-video.com/hls/lavall.m3u8', 441, 1, NULL, 1, 1, 'TV 4 La Vall#0');

-- ── TV5Monde Francia [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV5Monde Francia', 'https://graph.facebook.com/tv5mondeofficiel/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV5Monde Francia', @canal_id, 'https://ott.tv5monde.com/Content/HLS/Live/channel(europe)/variant.m3u8', 692, 1, 'TV5Monde.TV', 1, 1, 'TV5Monde Francia#0');

-- ── TV Almassora [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Almassora', 'https://graph.facebook.com/tvalmassora/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Almassora', @canal_id, 'https://play.turesportmedia.com/hls/abr_tvalmassora/index.m3u8', 441, 1, NULL, 1, 1, 'TV Almassora#0');

-- ── TV Aranda [Spain / Castilla y León] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Aranda', 'https://graph.facebook.com/575943555801687/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Aranda', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?network_id=12686&live=1', 441, 1, NULL, 1, 1, 'TV Aranda#0');

-- ── TV Artequatre [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Artequatre', 'https://graph.facebook.com/tvArtequatre/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Artequatre', @canal_id, 'https://streaming007.gestec-video.com/hls/artequatreTVA.m3u8', 441, 1, NULL, 1, 1, 'TV Artequatre#0');

-- ── TV Canaria (RTVC) [Spain / Canarias] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Canaria (RTVC)', 'https://graph.facebook.com/RadioTelevisionCanaria/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Canaria (RTVC) 1', @canal_id, 'https://d1oyt3v08gcy18.cloudfront.net/index-events.m3u8', 441, 1, 'Canarias.TV', 1, 1, 'TV Canaria (RTVC)#0'),
('TV Canaria (RTVC) 2', @canal_id, 'https://d2q93scm3qt8zp.cloudfront.net/index-events.m3u8', 441, 1, 'Canarias.TV', 1, 1, 'TV Canaria (RTVC)#1');

-- ── TV Canaria Net [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Canaria Net', 'https://graph.facebook.com/RadioTelevisionCanaria/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Canaria Net', @canal_id, 'https://d2q93scm3qt8zp.cloudfront.net/index-events.m3u8', NULL, 1, NULL, 1, 1, 'TV Canaria Net#0');

-- ── TV Costa Brava [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Costa Brava', 'https://graph.facebook.com/tvcostabrava/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Costa Brava', @canal_id, 'https://liveingesta318.cdnmedia.tv/costabravatvlive/smil:live.smil/playlist.m3u8', 441, 1, 'Xarxa_TV_Costa_Brava.TV', 1, 1, 'TV Costa Brava#0');

-- ── TV Cultura Brasil [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Cultura Brasil', 'https://graph.facebook.com/tvcultura/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Cultura Brasil', @canal_id, 'https://player-tvcultura.stream.uol.com.br/live/tvcultura.m3u8', NULL, 1, NULL, 1, 1, 'TV Cultura Brasil#0');

-- ── TVE Int. Europa [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVE Int. Europa', 'https://graph.facebook.com/tveInternacional/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVE Int. Europa', @canal_id, 'https://rtvelivestream-rtveplayplus.rtve.es/rtvesec/int/tvei_eu_main_dvr_720.m3u8', NULL, 1, 'TVE_INTER.TV', 1, 1, 'TVE Int. Europa#0');

-- ── TV Ferrol [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Ferrol', 'https://graph.facebook.com/tvferrol/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Ferrol', @canal_id, 'https://directo.tvferrol.es/tv.m3u8', 441, 1, NULL, 1, 1, 'TV Ferrol#0');

-- ── TV Florida USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Florida USA', 'https://graph.facebook.com/tvfloridausa/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Florida USA', @canal_id, 'https://stream-us-east-1.getpublica.com/playlist.m3u8?cb=[CACHEBUSTER]&network_id=16147&live=1&avod=0&hls_marker=1&pod_duration=120&ssai_enabled=1&content_network=streamingconnect&position=preroll&app_bundle=com.streamingconnect.viva&app_category=linear_tv&app_domain=mirametv.live&content_cat=IAB1&content_channel=tvflorida&content_genre=tv_broadcaster&content_rating=TV-G&content_id=mirametv_live&us_privacy=[US_PRIVACY]&gdpr=[GDPR]&min_ad_duration=6&max_ad_duration=120&ifa_type=[IFA_TYPE]', 542, 1, NULL, 1, 1, 'TV Florida USA#0');

-- ── TVG [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG', @canal_id, 'https://crtvg-europa.flumotion.cloud/playlist.m3u8', 441, 1, 'TVGAL.TV', 1, 1, 'TVG#0');

-- ── TVG 2 [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG 2', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG 2', @canal_id, 'https://crtvg-tvg2.flumotion.cloud/playlist.m3u8', 441, 1, 'TVG2.TV', 1, 1, 'TVG 2#0');

-- ── TVG Cativ@s [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Cativ@s', 'https://graph.facebook.com/oxabarinclub/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Cativ@s', @canal_id, 'https://crtvg-xabarinr2-schlive.flumotion.cloud/crtvglive/smil:channel7PRG.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Cativ@s#0');

-- ── TVGE 1 Guinea Ecuatorial [International / Int. África] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVGE 1 Guinea Ecuatorial', 'https://pbs.twimg.com/profile_images/1382981938231775232/-lv9ymLe_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVGE 1 Guinea Ecuatorial', @canal_id, 'https://rpn.bozztv.com/ses/tvge/tvge.smil/playlist.m3u8', NULL, 1, NULL, 1, 1, 'TVGE 1 Guinea Ecuatorial#0');

-- ── TVG Europa [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Europa', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Europa', @canal_id, 'https://crtvg-europa.flumotion.cloud/playlist_dvr.m3u8', NULL, 1, 'TVGA.TV', 1, 1, 'TVG Europa#0');

-- ── TVG Eventos [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Eventos', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Eventos 1', @canal_id, 'https://crtvg-events1.flumotion.cloud/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Eventos#0'),
('TVG Eventos 2', @canal_id, 'https://crtvg-events2.flumotion.cloud/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Eventos#1'),
('TVG Eventos 3', @canal_id, 'https://crtvg-events3.flumotion.cloud/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Eventos#2');

-- ── TVG Mira Radio Galega [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Mira Radio Galega', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Mira Radio Galega', @canal_id, 'https://crtvg-mirarg-schlive.flumotion.cloud/crtvglive/smil:channel1PRG.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Mira Radio Galega#0');

-- ── TVG Mociñ@s [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Mociñ@s', 'https://graph.facebook.com/oxabarinclub/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Mociñ@s', @canal_id, 'https://crtvg-xabarinr3-schlive.flumotion.cloud/crtvglive/smil:channel8PRG.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Mociñ@s#0');

-- ── TVG Pequerrech@s [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Pequerrech@s', 'https://graph.facebook.com/oxabarinclub/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Pequerrech@s', @canal_id, 'https://crtvg-xabarinr1-schlive.flumotion.cloud/crtvglive/smil:channel6PRG.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Pequerrech@s#0');

-- ── TVG Universo Castelao [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Universo Castelao', 'https://graph.facebook.com/CRTVG/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Universo Castelao', @canal_id, 'https://crtvg-universo-castelao-schlive.flumotion.cloud/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Universo Castelao#0');

-- ── TVG Xabarin [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVG Xabarin', 'https://graph.facebook.com/oxabarinclub/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVG Xabarin', @canal_id, 'https://crtvg-infantil-schlive.flumotion.cloud/crtvglive/smil:channel5PRG.smil/playlist.m3u8', 441, 1, NULL, 1, 1, 'TVG Xabarin#0');

-- ── TV Hellín [Spain / Castilla-La Mancha] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Hellín', 'https://graph.facebook.com/tvhellin/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Hellín', @canal_id, 'https://5940924978228.streamlock.net/directohellin/directohellin/playlist.m3u8', 441, 1, NULL, 1, 1, 'TV Hellín#0');

-- ── TVK Camboya [International / Int. Asia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVK Camboya', 'https://play-lh.googleusercontent.com/vleeuJK2i7TYUHvqoQfsujviGBQ1EdFw4kZXlMh6f2V07YQfdK5nDBWeWY5o1IrIQw=w200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVK Camboya 1', @canal_id, 'https://live.kh.malimarcdn.com/live/tvk.stream/playlist.m3u8', NULL, 1, NULL, 1, 1, 'TVK Camboya#0'),
('TVK Camboya 2', @canal_id, 'https://live.kh.malimarcdn.com/live/tvk2.stream/playlist.m3u8', NULL, 1, NULL, 1, 1, 'TVK Camboya#1');

-- ── TVM Córdoba [Spain / Andalucía] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TVM Córdoba', 'https://graph.facebook.com/TVM.Cordoba/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TVM Córdoba', @canal_id, 'https://d16pwlnz9msuji.cloudfront.net/wct-dad9c712-1c61-4a61-aeee-8be9f5c9e6e9/continuous/8fbfd2f5-30c2-4d48-a24e-73f5d35ba491/index.m3u8', 441, 1, NULL, 1, 1, 'TVM Córdoba#0');

-- ── TV Perú [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Perú', 'https://graph.facebook.com/TVPeruOficial/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Perú', @canal_id, 'https://cdnhd.iblups.com/hls/777b4d4cc0984575a7d14f6ee57dbcaf7.m3u8', NULL, 1, NULL, 1, 1, 'TV Perú#0');

-- ── TV Perú Noticias [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Perú Noticias', 'https://graph.facebook.com/noticias.tvperu/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Perú Noticias', @canal_id, 'https://cdnhd.iblups.com/hls/902c1a0395264f269f1160efa00660e47.m3u8', NULL, 1, NULL, 1, 1, 'TV Perú Noticias#0');

-- ── TV Rioja [Spain / La Rioja] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Rioja', 'https://graph.facebook.com/tvrtelevision/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Rioja', @canal_id, 'https://5924d3ad0efcf.streamlock.net/riojatv/riojatvlive/playlist.m3u8', 441, 1, 'TVR.TV', 1, 1, 'TV Rioja#0');

-- ── TV Sabadell Vallès [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('TV Sabadell Vallès', 'https://graph.facebook.com/tvsabadellvalles/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('TV Sabadell Vallès', @canal_id, 'https://ingest1-video.streaming-pro.com/canaltaronja/sabadell/playlist.m3u8', 441, 1, 'Xarxa_TV_Sabadell_Valles.TV', 1, 1, 'TV Sabadell Vallès#0');

-- ── Une Vinalopó [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Une Vinalopó', 'https://graph.facebook.com/UneVinalopo/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Une Vinalopó', @canal_id, 'https://streamingtvi.gestec-video.com/hls/unesd.m3u8', 441, 1, NULL, 1, 1, 'Une Vinalopó#0');

-- ── Univers TV [Spain / C. Valenciana] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Univers TV', 'https://graph.facebook.com/UniversValenciaDigital/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Univers TV', @canal_id, 'https://cloud2.streaminglivehd.com:1936/universfaller/universfaller/playlist.m3u8', 441, 1, NULL, 1, 1, 'Univers TV#0');

-- ── Urola TB [Spain / País Vasco] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Urola TB', 'https://graph.facebook.com/urolatelebista/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Urola TB', @canal_id, 'https://5940924978228.streamlock.net/j_Directo2/j_Directo2/playlist.m3u8', 441, 1, NULL, 1, 1, 'Urola TB#0');

-- ── V2Beat TV [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('V2Beat TV', 'https://graph.facebook.com/vtwobeat/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('V2Beat TV', @canal_id, 'https://abr.de1se01.v2beat.live/playlist.m3u8', NULL, 1, NULL, 1, 1, 'V2Beat TV#0');

-- ── Vallès Visió [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Vallès Visió', 'https://graph.facebook.com/tvvallesvisio/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Vallès Visió', @canal_id, 'https://liveingesta318.cdnmedia.tv/vallesvisiotvlive/smil:live.smil/playlist.m3u8?DVR', 441, 1, 'Xarxa_Valles_Visio.TV', 1, 1, 'Vallès Visió#0');

-- ── Venus Media Paraguay [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Venus Media Paraguay', 'https://pbs.twimg.com/profile_images/2053835972467367936/cDHyl_VX_200x200.jpg', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Venus Media Paraguay', @canal_id, 'https://tigocloud.desdeparaguay.net/venusmedia/venusmedia/playlist.m3u8', NULL, 1, NULL, 1, 1, 'Venus Media Paraguay#0');

-- ── Verbena TV España [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Verbena TV España', 'https://pbs.twimg.com/profile_images/1463159511133442059/uVV15n4k_200x200.jpg', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Verbena TV España', @canal_id, 'https://streamtv2.elitecomunicacion.cloud:3144/live/verbenatvlive.m3u8', NULL, 1, NULL, 1, 1, 'Verbena TV España#0');

-- ── Vinx TV [Spain / Deportivos] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Vinx TV', 'https://graph.facebook.com/VinxTV/picture?width=200&height=200', 1, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Vinx TV', @canal_id, 'https://live.enfoque.media:5443/live/streams/vinxtv.m3u8', 441, 1, 'Vinx.TV', 1, 1, 'Vinx TV#0');

-- ── VizionPlus Albania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('VizionPlus Albania', 'https://graph.facebook.com/vizionplustv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('VizionPlus Albania', @canal_id, 'https://tringliveviz.akamaized.net/delta/105/out/u/qwaszxerdfcvrtryuy.m3u8', NULL, 1, NULL, 1, 1, 'VizionPlus Albania#0');

-- ── VM Latino Costa Rica [International / Musicales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('VM Latino Costa Rica', 'https://graph.facebook.com/vmlatinocr/picture?width=200&height=200', 4, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('VM Latino Costa Rica', @canal_id, 'https://59ef525c24caa.streamlock.net/vmtv/vmlatino/playlist.m3u8', NULL, 1, NULL, 1, 1, 'VM Latino Costa Rica#0');

-- ── VOTV [Spain / Cataluña] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('VOTV', 'https://graph.facebook.com/votv.cat/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('VOTV', @canal_id, 'https://ingest2-video.streaming-pro.com/votv/streaming_web/playlist.m3u8', 441, 1, 'Xarxa_VOTV.TV', 1, 1, 'VOTV#0');

-- ── WDR Westdeutschen Alemania [International / Int. Europa] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('WDR Westdeutschen Alemania', 'https://pbs.twimg.com/profile_images/1368636275243315201/mWySSrnO_200x200.jpg', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('WDR Westdeutschen Alemania', @canal_id, 'https://wdrfsww247.akamaized.net/hls/live/2009628/wdr_msl4_fs247ww/master.m3u8', 723, 1, NULL, 1, 1, 'WDR Westdeutschen Alemania#0');

-- ── WeatherNation USA [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('WeatherNation USA', 'https://graph.facebook.com/WeatherNation/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('WeatherNation USA', @canal_id, 'https://d2ferbiwcx1539.cloudfront.net/v1/master/3722c60a815c199d9c0ef36c5b73da68a62b09d1/cc-8zd06wicndthf-ssai-prd/WNNationalSamsung/WNNationalSamsung.m3u8', 542, 1, NULL, 1, 1, 'WeatherNation USA#0');

-- ── WIPR Puerto Rico [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('WIPR Puerto Rico', 'https://graph.facebook.com/wiprtv/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('WIPR Puerto Rico', @canal_id, 'https://streamwipr.pr/hls/stream/index.m3u8', NULL, 1, NULL, 1, 1, 'WIPR Puerto Rico#0');

-- ── WTV Nicaragua [International / Int. América] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('WTV Nicaragua', 'https://graph.facebook.com/WTVNicaraguacanal20/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('WTV Nicaragua', @canal_id, 'https://cloudvideo.servers10.com:8081/8130/index.m3u8', NULL, 1, NULL, 1, 1, 'WTV Nicaragua#0');

-- ── Xunta de Galicia [Spain / Galicia] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('Xunta de Galicia', 'https://graph.facebook.com/@xuntadegalicia/picture?width=200&height=200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('Xunta de Galicia', @canal_id, 'https://xuntalive.akamaized.net/hls/live/2032287/streamxunta/bitrate_2.m3u8', 441, 1, NULL, 1, 1, 'Xunta de Galicia#0');

-- ── ¡HOLA! Play [Spain / Eventuales] (canal nuevo) ──
INSERT INTO canales (nombre, logo, category, activo) VALUES ('¡HOLA! Play', 'https://yt3.ggpht.com/ytc/AMLnZu_Hd2WYs49wYCBMAphVpvvpBzd-EJFU8XFQgccPIw=s200', 3, 1);
SET @canal_id = LAST_INSERT_ID();
INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref) VALUES
('¡HOLA! Play', @canal_id, 'https://hola-play-2108fd06-86d4-44e8-9867-c35b4895a1c1-es.fast.rakuten.tv/v1/master/0547f18649bd788bec7b67b746e47670f558b6b2/production-LiveChannel-6433/master.m3u8', 441, 1, NULL, 1, 1, '¡HOLA! Play#0');

COMMIT;