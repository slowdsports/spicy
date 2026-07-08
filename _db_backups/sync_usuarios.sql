SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- usuarios
-- ============================================================
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','usuario','spicy') NOT NULL DEFAULT 'usuario',
  `avatar_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `spicy_hasta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `avatar_url`, `activo`, `created_at`, `updated_at`, `spicy_hasta`) VALUES
(1, 'Tele Deportes', 'slowdsports@gmail.com', '$2y$10$amYQlIjHAQDkGEGOkuYky.ka8QBTnBYjgCVOqClcMJV4IDHoqiEQG', 'admin', NULL, 1, '2026-04-22 01:01:40', '2026-05-09 17:53:19', NULL),
(2, 'Tele Deportes', 'jet_1968@proton.me', '$2y$10$nZodQ8U1ZXc/ikWO8h6zn.6J2KVkMarcGRjvCR1dGCIeFwG36Ez2.', 'admin', NULL, 1, '2026-04-29 01:54:08', '2026-05-09 17:53:24', NULL),
(3, 'Alvaro', 'alvarojesus.9720@gmail.com', '$2y$10$iqU8TIkpz1AZom6VvejaQ.uXuH3VYM/i4IwW0vgAzlj7lqnBNf5pq', 'usuario', NULL, 1, '2026-04-30 17:18:17', '2026-04-30 17:18:17', NULL),
(4, 'Oualid Interista', 'interistaoualid@gmail.com', '$2y$10$404Lkti7lYSuRfdiJqyVM.HQIpQXADOxPHieCNaexQDt9dK6FbOoa', 'usuario', NULL, 1, '2026-05-01 08:21:00', '2026-05-01 08:21:00', NULL),
(5, 'Miguel López', 'kuarzo74@gmail.com', '$2y$10$2Zjl06RBS1o7/UWbBzEn5Obh5Sxl20EGf4jC4xs1Jqe7AYRD05t/G', 'usuario', NULL, 1, '2026-05-01 11:12:01', '2026-05-01 11:12:01', NULL),
(6, 'Allyson', 'lohinew498@inreur.com', '$2y$10$iOj4ipc.FG4PZ5dQ1tlp9uJKwgesqwojrlwPslH8QjLffzXLRg5qy', 'usuario', NULL, 1, '2026-05-01 12:07:31', '2026-05-01 12:07:31', NULL),
(7, 'frodo', 'frodo3580@gmail.com', '$2y$10$2056oBmnutTsCOE8ck6Rsel3oTygemL7/DhRuafqyK/DrBAGXfd66', 'usuario', NULL, 1, '2026-05-01 12:55:00', '2026-05-01 12:55:00', NULL),
(8, 'Jose Luis Gomez', 'luisgomezgz@gmail.com', '$2y$10$5UfcmuPHVyMtLzn79Kq99uv5ru9Q2Yx5CUD85FYr5ABvjLhjqf7be', 'usuario', NULL, 1, '2026-05-01 18:38:32', '2026-05-01 18:38:32', NULL),
(9, 'Ignacio agudelo Q', 'nachin3358@gmail.com', '$2y$10$k7BVLjjbmIntgM2yaTNdq.ySWPPhewWqisr5kgMnbhiAXQ2kHb1Pm', 'usuario', NULL, 1, '2026-05-01 19:45:52', '2026-05-01 19:45:52', NULL),
(10, 'Antonio Amoros', 'tone16868@gmail.com', '$2y$10$AYXTeRhwLwbC/UPbs7Yl9.l.kkt6E4bfVs.TQg0R8Xb5DaoedFxI.', 'usuario', NULL, 1, '2026-05-02 16:16:30', '2026-05-02 16:16:30', NULL),
(11, 'Duvan', 'duvanfelipegue3@gmail.com', '$2y$10$62XZ5nupb.BYcY3FejYuHOo2TyK6B.aSG9wE14RcOPDIX/AEgSW7K', 'usuario', NULL, 1, '2026-05-02 18:48:02', '2026-05-02 18:48:02', NULL),
(12, 'Cristian', 'maria12fatimaqnde@gmail.com', '$2y$10$BGzNqf6HmZQ1c6kegW4C7eFb/C7T8WQojvGLByQUdxheHcf1Jw6D.', 'usuario', NULL, 1, '2026-05-03 00:47:46', '2026-05-03 00:47:46', NULL),
(13, 'Javi', 'xaviermejia801@gmail.com', '$2y$10$SJVTlVNYEfsDCmWZ8WIhUeNGT/EUm5XOOqG2rri.QN2RlnH7BZo5u', 'usuario', NULL, 1, '2026-05-03 15:17:36', '2026-05-03 15:17:36', NULL),
(14, 'José Oropeza', 'joseoropeza2@gmail.com', '$2y$10$byIH83ntO3K3atOBZ5e1dufhEAJvOXBYp18VmI7nhWf923XWsBcw.', 'usuario', NULL, 1, '2026-05-03 16:29:28', '2026-05-03 16:29:28', NULL),
(15, 'McArron67', 'carmelosilva@gmail.com', '$2y$10$LB2xsvur9pOSGKsmnD6zx.w6GGxc6PMbIOspeKT2KVwgpxQlkfSi2', 'usuario', NULL, 1, '2026-05-03 17:19:13', '2026-05-03 17:19:13', NULL),
(16, 'Kendry González Torres', 'kendrygonzaleztorres2@gmail.com', '$2y$10$.04zinbiTVwOLxmOv93d9e2s.ysXlN7SAvpaNT7nsQOzchXZwuvq.', 'usuario', NULL, 1, '2026-05-03 20:10:29', '2026-05-03 20:10:29', NULL),
(17, 'fer', 'fernandomoreco94@gmail.com', '$2y$10$sh4NZ5norQrxrw1iLEJumOwFVjbn9K7h.SWfIm0Dk.fuzDll9T7h2', 'usuario', NULL, 1, '2026-05-09 00:08:05', '2026-05-09 00:08:05', NULL),
(18, 'David Cortez', 'dc8874068@gmail.com', '$2y$10$x5Gz2/c0tAPKELwtJoqqCuyZrNmFVVzBq3tJlNQQcodxaoJgcILim', 'usuario', NULL, 1, '2026-05-09 00:40:41', '2026-05-09 00:40:41', NULL),
(19, 'Luiti10', 'gaditanocarnavalero10@gmail.com', '$2y$10$g6w84b02uzsDWV78ZCZoGeRgddMuxFjbhmwJ.vLFhpPahtwqibNk2', 'usuario', NULL, 1, '2026-05-09 12:44:32', '2026-05-09 12:44:32', NULL),
(20, 'Tittyshev', 'martinleontomas@gmail.com', '$2y$10$.4bmgSWx1T92Otuu.n0Vh.VDIIrw1vFi7etdWvaVK8MNkK7UZxPKq', 'usuario', NULL, 1, '2026-05-09 13:01:33', '2026-05-09 13:01:33', NULL),
(21, 'peke', 'spiderdox18@gmail.com', '$2y$10$bVRJjFtB47L1iNPXMCZDXuVIEfakYvWjFNorNFx6m5G63p/aqidTi', 'usuario', NULL, 1, '2026-05-09 15:54:39', '2026-05-09 15:54:39', NULL),
(22, 'Daniel Garcia Brito', 'danielgarciabrito26@gmail.com', '$2y$10$VxahuvFVcveBUNh6njvXMujUqIEGOWGBfFOA3u2.ghzpuTqx.J28q', 'usuario', NULL, 1, '2026-05-10 01:13:19', '2026-05-10 01:13:19', NULL),
(23, 'Fran Mejías Morillo', 'franciscomejiasmorillo@gmail.com', '$2y$10$PPJux2/4dRkxRq7GbGHNNuhtzEkanqpc70AenkuAzflODHo1nbUx6', 'usuario', NULL, 1, '2026-05-10 18:03:06', '2026-05-10 18:03:06', NULL),
(24, 'kevin', 'kevinhoramas2009@gmail.com', '$2y$10$P4xNjjfn.40nph8kTS92Su/2rO9gWxxD159KgZ3yxoj0TgZCa228i', 'usuario', NULL, 1, '2026-05-10 18:53:27', '2026-05-10 18:53:27', NULL),
(25, 'Felix GooL', 'chukyoasis75@gmail.com', '$2y$10$JooFftO2cipj3y/Yo0.BC.87O2LTc5I4MMmOhjmAhXWfxzgNR/shK', 'usuario', NULL, 1, '2026-05-10 19:15:14', '2026-05-10 19:15:14', NULL),
(26, 'daniela', 'danielaordunasese@gmail.com', '$2y$10$qpKsN7PhwzowmW1B3g9WaeVT4pl6qtX7jK0hQ2RNm5hJ1gmNxIfLm', 'usuario', NULL, 1, '2026-05-10 20:07:22', '2026-05-10 20:07:22', NULL),
(27, 'Alexander Angulo', 'alexanderangulo10@hotmail.com', '$2y$10$Lsp3pL.EYrTBZqag6E818urnq7qHh.XclToxhZZxfT8UeMRLbDwc6', 'usuario', NULL, 1, '2026-05-12 18:57:58', '2026-05-12 18:57:58', NULL),
(28, 'Fernando', 'fergutinorteb@gmail.com', '$2y$10$jQ1pLMCymTPglKIYgqx7j.w9L7378GSS7QE9r1K5PGQlA1pCNk/xi', 'usuario', NULL, 1, '2026-05-13 18:44:49', '2026-05-13 18:44:49', NULL),
(29, 'Jose', 'joseignaciomardia2016@gmail.com', '$2y$10$iTDKsaeic8isHlkyixPIHuex1BRiqa53xbe3hZprsLWDSpPJKOfFG', 'usuario', NULL, 1, '2026-05-16 08:28:49', '2026-05-16 08:28:49', NULL),
(30, 'VIVIANA EDITH GRAMAJO', 'benfra36@gmail.com', '$2y$10$vcCLgb/c9/5pbNCxGLhYF.GhSP7yJljo0S9gOVJrYyMriHwiItZL2', 'usuario', NULL, 1, '2026-05-26 07:11:16', '2026-05-26 07:11:16', NULL),
(31, 'Wilmar barrios', 'wilmarbarrios59@gmail.com', '$2y$10$EXdFpWqzwqsEI542cbPAvOQk.jAEDwsVf5S6eK/u45O4VO72jzVku', 'usuario', NULL, 1, '2026-05-27 03:52:27', '2026-05-27 03:52:27', NULL),
(32, 'Esteban Tobar', 'estebantobar791@gmail.com', '$2y$10$Nt0Q7akPsDhoRJUqkwADs.swO0NWZpxsygceFv/HpSDViPPiU4dGG', 'usuario', NULL, 1, '2026-05-29 15:22:30', '2026-05-29 15:22:30', NULL),
(33, 'loop sedge', 'animxtv341@gmail.com', '$2y$10$XEfnrG02FOHnasDReVk9veuZ.wbpAMFVgc5M5NeG115hHWpRuqFA.', 'usuario', NULL, 1, '2026-05-29 20:38:38', '2026-05-29 20:38:38', NULL),
(34, 'tomas enrique quiroga diaz', 'tomyly88@hotmail.com', '$2y$10$oE7PA8QMzhGSJtvRD.jjxeRnmwAE8zBFKCyKgerXx7sYU7Zo65JcC', 'usuario', NULL, 1, '2026-06-06 19:26:55', '2026-06-06 19:26:55', NULL),
(35, 'aaa', 'amcave360@hotmail.com', '$2y$10$73uy.eZbvktEECi7CN.Hzu5L.X2miUFw4S8pAv8pAm81Wv81ylqPe', 'usuario', NULL, 1, '2026-06-06 19:37:08', '2026-06-06 19:37:08', NULL),
(36, 'Daniperezddddd', 'daniperezdddddd@gmail.com', '$2y$10$pRSLpZhw3rIiWHC8Am3x7OvlxZqAZ184udrSx.y4NN5Zu/.qkhLw.', 'usuario', NULL, 1, '2026-06-07 10:04:04', '2026-06-07 10:04:04', NULL),
(37, 'Mikel', 'pitimunoz1@gmail.com', '$2y$10$peDgBB8jYWTo1zt08AEb/.75Ssfe0XsRO1j6EU6MT7AswQIY0p9NW', 'usuario', NULL, 1, '2026-06-07 13:11:39', '2026-06-07 13:11:39', NULL),
(38, 'Sergio Gomezz', 'elchunoyt@gmail.com', '$2y$10$fCjGOPszpDr0L9ez9HB32OUVziTCi/l8LM.8tdWJhxLCotCyQnc1O', 'usuario', NULL, 1, '2026-06-07 16:05:26', '2026-06-07 16:05:26', NULL),
(39, 'Rember lopez', 'rember.lopez243@gmail.com', '$2y$10$KaBxN6f5Ver5AJnKCmURkuLiex95ZIFO3hit3Bg49jpP9u05gWyKa', 'usuario', NULL, 1, '2026-06-07 16:51:19', '2026-06-07 16:51:19', NULL),
(40, 'Maider Goñi', 'maidergoni@gmail.com', '$2y$10$n/kAaRcIzZ.s/8AzCMwUy.1u/1dnZ2kFPRFNB7QoKEzjwRw9tbZbi', 'usuario', NULL, 1, '2026-06-07 17:04:38', '2026-06-07 17:04:38', NULL),
(41, 'Antonio José', 'bitequisle@gmail.com', '$2y$10$ExfuUPparLN9W2JLoJJeL.T67VLMIVqYW02rOcKo2uKS0Kqmjmzdu', 'usuario', NULL, 1, '2026-06-07 19:10:31', '2026-06-07 19:10:31', NULL),
(42, 'Samuel García Ruiz', 'samuelitohearts@gmail.com', '$2y$10$8S1Yy89C.11C7tEXFzPdM.bf9XNpnKU.y9eFSmzY91Z8y./BvfRP6', 'usuario', NULL, 1, '2026-06-07 19:13:12', '2026-06-07 19:13:12', NULL),
(43, 'Jajaja', 'dakosi1272@5nek.com', '$2y$10$LbIRrDSR30rgGQScof2dGu2WUuzydz3dr0wZOrExjZLntWYRiyZIO', 'usuario', NULL, 1, '2026-06-07 19:34:10', '2026-06-07 19:34:10', NULL),
(44, 'amag', 'leioamaite13@gmail.com', '$2y$10$MTpEiTlCXRSECrjHjrKI8eTCGL4Cra154N8IRjZGZIvwUzLPq5p8i', 'usuario', NULL, 1, '2026-06-08 20:04:36', '2026-06-08 20:04:36', NULL),
(45, 'santiago narvaez', 'rocos7m@gmail.com', '$2y$10$nXKsP0eX3UpxANjpqr1KqOEJrjgEcZkK8BvYPHpjRPTGGvth0aZhi', 'usuario', NULL, 1, '2026-06-08 22:01:37', '2026-06-08 22:01:37', NULL),
(46, 'Kendry González Torres', 'kendrygonzaleztorres3@gmail.com', '$2y$10$JZnETj.rCMpfU7VOnt/.1uFGsHUhikoAigdh6gspbKKSVUC1SB8Mm', 'usuario', NULL, 1, '2026-06-09 04:27:06', '2026-06-09 04:27:06', NULL),
(47, 'Wenceslao Cora', 'roda.70wcf@gmail.com', '$2y$10$AxypeAULVndt3k1bdnuvM.0gCVvO.gde3qkumMBvXfXzpGEEseGRq', 'usuario', NULL, 1, '2026-06-09 10:00:00', '2026-06-09 10:00:00', NULL),
(48, 'Rafa Perez', 'raivza@hotmail.com', '$2y$10$KhtZgCk6RCa9hADokydX/.NydbgKyJ7LSoo7B6QL4..NNgGWrdKyW', 'usuario', NULL, 1, '2026-06-09 22:27:31', '2026-06-09 22:27:31', NULL),
(49, 'tomigllo', 'leaofn11@gmail.com', '$2y$10$SmCBqvpyvAXb6nZtl/ITdelTUYPuZGTux7E2pefRw39bKx04QzdWi', 'usuario', NULL, 1, '2026-06-10 01:08:07', '2026-06-10 01:08:07', NULL),
(50, 'Jkamrik', 'jkamrik@gmail.com', '$2y$10$QMHoFwP76qp35ebClSKg4OkQ0zOGkudt4qd3lUbp0Uu/Pj.oOHPRW', 'usuario', NULL, 1, '2026-06-10 19:04:07', '2026-06-10 19:04:07', NULL),
(51, 'Airpod4325', 'airpod4325@gmail.com', '$2y$10$LWW61mM79RTERNN1L1b1.u5mr4llGjhpDfpR2GBHIYFTZGMwZOyVG', 'usuario', NULL, 1, '2026-06-10 19:11:19', '2026-06-10 19:11:19', NULL),
(52, 'Jaime Merino González', 'merinogonzalezjaime@gmail.com', '$2y$10$7dK0KnXM/u0DYN6syC3SH.ZAr4f1BjFwNcp32M47eN5yJOnvysNRK', 'usuario', NULL, 1, '2026-06-10 19:16:08', '2026-06-10 19:16:08', NULL),
(53, 'Alfredo', 'almarzuiviz@hotmail.com', '$2y$10$YBBYNdCvpg9odPg0BqZcO.3hxwT171h.roe1B.lTrCMsu7DQxjZbm', 'usuario', NULL, 1, '2026-06-10 19:18:33', '2026-06-10 19:18:33', NULL),
(54, 'Jose', 'jmlopezespa@gmail.com', '$2y$10$xL.1VuRV4N6iCUKQuBIX6uYTJeMQ8fqyui.6Z1KvwckZlkTryROsq', 'usuario', NULL, 1, '2026-06-10 19:20:40', '2026-06-10 19:20:40', NULL),
(55, 'holaa', 'domeno2766@5nek.com', '$2y$10$E90n/awMMaoWYEXGZFi7Bu0GXi608eapuykLuTfaVN/a4okXhGKVO', 'usuario', NULL, 1, '2026-06-10 19:28:28', '2026-06-10 19:28:28', NULL),
(56, 'Antonio', 'antoniobcordoba@gmail.com', '$2y$10$.p7SboLfVHUutQUp3s6LnusHEgvwzt9/TK3944vC1.gkCgxQhrh2G', 'usuario', NULL, 1, '2026-06-10 19:37:49', '2026-06-10 19:37:49', NULL),
(57, 'Noelia García Villanueva', 'ngarciavillanueva10@gmail.com', '$2y$10$f4bDO.ferxGn3sS/zg8R4OVYUl.D788AczQ3H95tSfYFwYKOSLC5a', 'usuario', NULL, 1, '2026-06-10 19:40:57', '2026-06-10 19:40:57', NULL),
(58, 'manolo', 'portillo2022@hotmail.com', '$2y$10$f208U1vLbigjIrxoDfsYL.OhN28AX.sKMA.LVIDfLIjLWM3HlRRnu', 'usuario', NULL, 1, '2026-06-10 19:47:34', '2026-06-10 19:47:34', NULL),
(59, 'Jorge', 'bodazaragozaweb@gmail.com', '$2y$10$9XCHTE5lDCS1dKDjZvsLYO5n5KvVdZ4McTnEgw.IZAvXgPSdqdShm', 'usuario', NULL, 1, '2026-06-10 19:49:37', '2026-06-10 19:49:37', NULL),
(60, 'alberto ortiz de lazkano viana', 'zarrakuskina@gmail.com', '$2y$10$BV7Y7b0thNOe42CL3vkJWOQH7uWTDkVjeLiIftjd7Ax0memt5iDAK', 'usuario', NULL, 1, '2026-06-10 19:55:30', '2026-06-10 19:55:30', NULL),
(61, 'David Ramírez', 'cara.feudal-7t@icloud.com', '$2y$10$wmms2Cc/zUAQrMFubdKqL.qAlTWfghBfvuGZ1bevVmLHC7rn4g7ta', 'usuario', NULL, 1, '2026-06-10 20:12:01', '2026-06-10 20:12:01', NULL),
(62, 'José Vergel', 'joseluis141@gmail.com', '$2y$10$lDYgLSBIQip29z7j0c6Q2.vM.Xqq9jwjTRvuAMBEHmy8FBI18VNkO', 'usuario', NULL, 1, '2026-06-10 20:42:33', '2026-06-10 20:42:33', NULL),
(63, 'Alejandro Soto', 'alejandrosotozerzi@gmail.com', '$2y$10$qtN3oYGzUxaJ8ya6oz5MOefNN/N3gZzZaLhEnQYvRQvetzCuCqhTa', 'usuario', NULL, 1, '2026-06-10 20:58:22', '2026-06-10 20:58:22', NULL),
(64, 'Cero', 'deciles65.voces@icloud.com', '$2y$10$bTrJE.hGrBqkNHa1BsHUquglGqZV7ohG5tn3x2NvGAr9tKtSzgcb2', 'usuario', NULL, 1, '2026-06-10 21:05:04', '2026-06-10 21:05:04', NULL),
(65, 'Xavier Ruiz Perez', 'xavieltasr4@gmail.com', '$2y$10$X/4MYoH3PjlHeLn9FPRRhOXvZSEqUDMqbVc.CZ/Ct0zGDh.atr/aW', 'usuario', NULL, 1, '2026-06-11 00:15:39', '2026-06-11 00:15:39', NULL),
(66, 'Antonio Pérez', 'cardenas78@hotmail.es', '$2y$10$zIh4vFWxejp0V4ojbxs9Ze3q/ojZO2OPcyJKrwQI3kvUI3/QqY3OG', 'usuario', NULL, 1, '2026-06-11 09:19:44', '2026-06-11 09:19:44', NULL),
(67, 'marlo', 'j6451991@gmail.com', '$2y$10$G1bMFlJppF2IvVI5sk8V..4YMiHGKdsE8au21tWZg/S27mZW1z5RW', 'usuario', NULL, 1, '2026-06-11 12:46:52', '2026-06-11 12:46:52', NULL),
(68, 'Eliseo', 'cheomorfrank@gmail.com', '$2y$10$LrmkSi1.0FiSEjywzwd8euUF.dU2a02bFAGd4kdT9OiyE6.SKPopi', 'usuario', NULL, 1, '2026-06-11 13:33:25', '2026-06-11 13:33:25', NULL),
(69, 'Jose Villanueva', 'villanuevabayardo28@gmail.com', '$2y$10$PbU5fsFphp/jgzWFmIUyHe/r.Cp2KALARN4VA9NuzS6rpaqaNwpPC', 'usuario', NULL, 1, '2026-06-11 14:36:22', '2026-06-11 14:36:22', NULL),
(70, 'Alex Moncada', 'cofih.hn@gmail.com', '$2y$10$ruTAOf/viK6srrJFv73MoOfrUGZMCpG0zqWWFCY4NDz3.luLziM0O', 'spicy', NULL, 1, '2026-06-11 17:37:55', '2026-06-17 16:28:34', '2026-07-11 17:39:44'),
(71, 'manuela ferran', 'ferranmanuela@gmail.com', '$2y$10$k0kKCk7IsgGaR.9WGK8cNOBobPZ3aU7n5/o8YlrZE0EqM46rXw6w6', 'usuario', NULL, 1, '2026-06-11 17:51:43', '2026-06-11 17:51:43', NULL),
(72, 'ANHUAR COWO', 'elanhuar2004@gmail.com', '$2y$10$4hQUCSEL4mHjgIJ3ti1Oq.aiKETfFcTbaBOMIf.1bZ0zZ4y.u.B66', 'usuario', NULL, 1, '2026-06-11 18:40:46', '2026-06-11 18:40:46', NULL),
(73, 'neco9', 'ivanfaampu@gmail.com', '$2y$10$JnsGiGkl.zEq6PPIaDheT.L1SQvqxaYb3wvTQyVuWmd24UrKATfSS', 'usuario', NULL, 1, '2026-06-11 18:53:07', '2026-06-11 18:53:07', NULL),
(74, 'Alejandro Ruiz', 'alebernalruiz@gmail.com', '$2y$10$rqYsMulSbMALjmyUGth9JOWUcn2OlgaMhUNywUh5aigH/HhwOwxJW', 'usuario', NULL, 1, '2026-06-12 17:27:13', '2026-06-12 17:27:13', NULL),
(75, 'Noé Escalante', 'noefernandoe84@gmail.com', '$2y$10$s4vMuccnNdi8zSXMiYUcEeDReOk7B5Ad/J4rO1p/e60W.YulrvE76', 'spicy', NULL, 1, '2026-06-12 19:09:56', '2026-06-15 18:00:29', '2026-07-15 18:00:29'),
(76, 'Marco', 'sincamino1097@gmail.com', '$2y$10$yaXrO0RAJem/iEE08pnATOEfzwFr79it3v4qjKhGmKbSqn8BhEzjC', 'usuario', NULL, 1, '2026-06-13 19:22:31', '2026-06-13 19:22:31', NULL),
(77, 'Duvan', 'duvan526urbano@gmail.com', '$2y$10$lPkAupt/ff5fdNrLytyhEuJlZ7FVM4LzfzBgJzxZOp0cPjHLtN5xu', 'usuario', NULL, 1, '2026-06-13 21:04:37', '2026-06-13 21:04:37', NULL),
(78, 'Josh Fran Gutierrez', 'staryuukliiqueonda@gmail.com', '$2y$10$e4B.UdeSUFPuxEn3/deM2uIdS4DrtRyWLViKNkkDWS5h4zrwZf6He', 'usuario', NULL, 1, '2026-06-14 17:58:15', '2026-06-14 17:58:15', NULL),
(79, 'Johan', 'johanrivera127@gmail.com', '$2y$10$34E/VgHMrpB5U3U5ojDnmux7NUjqI06jOyztBe7sDgBQ6XLCbN4/W', 'usuario', NULL, 1, '2026-06-14 19:03:46', '2026-06-14 19:03:46', NULL),
(80, 'CARLOS SERNA RAMIREZ', 'sernacarlos811@gmail.com', '$2y$10$vDtl3VHgu.BE8Hne/vyVru8owLe94PQwLdxPqOMPY0VUD8tQFUaem', 'usuario', NULL, 1, '2026-06-15 22:04:51', '2026-06-15 22:04:51', NULL),
(81, 'Erinson', 'incatamanie@gmail.com', '$2y$10$vh4pf/4VyRGCNmtCIVN0WuqWxkLfoikSliivn3mwSPJX3hqQ8D2iW', 'usuario', NULL, 1, '2026-06-15 23:27:22', '2026-06-15 23:27:22', NULL),
(82, 'Jose Villafaña', 'josemateov@gmail.com', '$2y$10$9StGbzKP5yZCFtM0tHiHWeIS9955algOFhuvsOcsFx.N3w9roC3o2', 'usuario', NULL, 1, '2026-06-16 01:01:46', '2026-06-16 01:01:46', NULL),
(83, 'Bernardo Cardales', 'bernardohernandezcardales@outlok.com', '$2y$10$8BpJ1IGuRpT3FmqWqeUQHu7GI9whUojz.SBvh9id.mpfFirNydFCS', 'usuario', NULL, 1, '2026-06-16 12:54:01', '2026-06-16 12:54:01', NULL),
(84, 'Jösean Seiya', 'eldedoddios@gmail.com', '$2y$10$8M8SujglRKH0g0TBuwsu2endmAjZ5Vdlqn/CQnBSraBykxiXP2o4a', 'usuario', NULL, 1, '2026-06-16 18:29:30', '2026-06-16 18:29:30', NULL),
(85, 'Alysson Moncada', 'alywmonkda_2007@hotmail.com', '$2y$10$u0jX4li34cI.bmaGRYiX.OFKRq2KvbC07CMLeqRHRqqpaOtNP6ABy', 'spicy', NULL, 1, '2026-06-17 02:12:36', '2026-06-17 14:54:14', '2026-07-17 14:54:14'),
(86, 'Marlon Orellana', 'marlon.mgor@gmail.com', '$2y$10$8bM8yP0oasIBS1J.WwNPEO76o9T0AOwnfLN0EtV.k7jJeX5Az1bXK', 'spicy', NULL, 1, '2026-06-17 03:13:54', '2026-06-17 03:15:45', '2026-07-17 03:15:45'),
(87, 'Josue Salvador', 'josuesalvatore491@gmail.com', '$2y$10$izPTQDAIEGKWL.xQr6mL9uGYZRu9.q5eY0XkhbwZlMyO.3Cv5LykW', 'usuario', NULL, 1, '2026-06-17 16:52:30', '2026-06-17 16:52:30', NULL),
(88, 'Angel', 'hernandezjesus2405@gmail.com', '$2y$10$SPOPg.zwkJh4TbbKLgCbyeuRYD8SfogKIPHK1nSy1GvfAooruiF0S', 'usuario', NULL, 1, '2026-06-17 18:00:47', '2026-06-17 18:00:47', NULL),
(89, 'Alexander Moncada', 'cofirh.hn@gmail.com', '$2y$10$Z/QWZFfcQrkNifxjTSee2.vj4ovv3WyRKOXqjv.l0QtHtD8V//WC.', 'usuario', NULL, 1, '2026-06-18 03:35:40', '2026-06-18 03:35:40', NULL),
(90, 'Erick Sanchez', 'erickger10@gmail.com', '$2y$10$hqlOG1WzugINuv8Iu129aOWAju7Djom/u.x7qyQrHhBuH4A3RxEGG', 'usuario', NULL, 1, '2026-06-18 20:01:01', '2026-06-18 20:01:01', NULL),
(91, 'Marco Css', 'mrcs9263@gmail.com', '$2y$10$Wf.SGv5griHkx6RzLQ.1JuTlFPX2YCI25xOo8larqtEkP8AZjTB6e', 'usuario', NULL, 1, '2026-06-18 22:18:16', '2026-06-18 22:18:16', NULL),
(92, 'Roba mpds', 'robampds231@gmail.com', '$2y$10$ZoXV0A1YPbVdDLRTinBwzO6lXu1mtIh8Jf8zATJcgJeOZ2pKQR/Ua', 'usuario', NULL, 1, '2026-06-19 22:37:20', '2026-06-19 22:37:20', NULL),
(93, 'Rodrigo', 'drodrigofuenzalida@gmail.com', '$2y$10$kO4ViL.1wxopITMbqhbl6etkRlVv2k/x68yxC92APbyFgleEYISei', 'usuario', NULL, 1, '2026-06-20 01:30:42', '2026-06-20 01:30:42', NULL),
(94, 'Frank Erazo', 'frank_128@hotmail.es', '$2y$10$W2IjmExTR3edyivzo3VxgeMalLDHtK2oDVZPeqgsGLspfdPcy4d5a', 'usuario', NULL, 1, '2026-06-20 11:05:40', '2026-06-20 11:05:40', NULL),
(95, 'WG', 'jhort2355@gmail.com', '$2y$10$dGPxIlx66f0/rnpalVpoduDkv.RFaR.XschADt5jDlgUTPgWoDhsK', 'usuario', NULL, 1, '2026-06-20 16:39:20', '2026-06-20 16:39:20', NULL),
(96, 'Kasti Castillo', 'kakastillo@gmail.com', '$2y$10$cXJxB6NzJthPAFWbcQ9KyORmPPEwtdQk/c5YO2NLaYhbE9t.0TLGW', 'usuario', NULL, 1, '2026-06-20 19:23:20', '2026-06-20 19:23:20', NULL),
(97, 'jeanpier hernandez aybar', 'hernandezaybarjeanpier@gmail.com', '$2y$10$GksizZreCpzdOTywLvTeF.w51Dt36uK8o4yavC.9AMlzgCU6wYjUO', 'usuario', NULL, 1, '2026-06-21 00:18:04', '2026-06-21 00:18:04', NULL),
(98, 'Mircia', 'mirciasoza23@gmail.com', '$2y$10$Hz9UAc0vCByB9Xe4D9wsPumsrLiqUoWkDMQl.6owg0KbRukt.HUSS', 'usuario', NULL, 1, '2026-06-21 01:30:58', '2026-06-21 01:30:58', NULL),
(99, 'Agustin lopez', 'michericalopeez@gmail.com', '$2y$10$EXavX.wtcvHzWMsjIl7EV.ewQfactwTMLCeXB8QYT/ai.Pr8qGxXG', 'usuario', NULL, 1, '2026-06-23 00:39:32', '2026-06-23 00:39:32', NULL),
(100, 'Chukwuemeka Okoronkwo', 'emeka.okoronkwo@hotmail.com', '$2y$10$RkSie0XaOvQhc20ql8qLw.0iNRSpyLqvCezn7EwTVz2IQufOk9bUG', 'usuario', NULL, 1, '2026-06-23 08:13:32', '2026-06-23 08:13:32', NULL),
(101, 'Caleb Hernandez', 'cottonnights07@gmail.com', '$2y$10$tBF0O4IUAnz6uXiCihrCf.um6GYMHSiaBmezRuSBn./u5uxtApwcq', 'usuario', NULL, 1, '2026-06-23 15:00:14', '2026-06-23 15:00:14', NULL),
(102, 'Buds Untld', 'budslab@gmail.com', '$2y$10$kMCr0KcHowEyff1Izkt9AuxzAJnMc3V21i5dz60MGEXrWJUhlAlQe', 'usuario', NULL, 1, '2026-06-23 19:14:19', '2026-06-23 19:14:19', NULL),
(103, 'Julio', 'juliocesardiaz64@gmail.com', '$2y$10$qzHdBaQ.hq/9GTkvUobwreeeqYmj8PDSw//15JemGdPCppP42Qd2a', 'usuario', NULL, 1, '2026-06-23 23:59:15', '2026-06-23 23:59:15', NULL),
(104, 'Duvan', 'duvan527urbano@gmail.com', '$2y$10$EAhwxmwOQocK4UVVcDagR.2cMbpftj3YzUF7aJQnoyYnoCtZqCw0q', 'usuario', NULL, 1, '2026-06-25 01:56:18', '2026-06-25 01:56:18', NULL),
(105, 'Edgar', 'leobardoedgar8@gmail.com', '$2y$10$WVm1OemXa1b3pDn3t52Yn.4ewlL21NDAhdruiHhDYHS0umw.EuQV6', 'usuario', NULL, 1, '2026-06-26 03:55:59', '2026-06-26 03:55:59', NULL),
(106, 'erick jeancarlos cordova reyes', 'cordovaerick079@gmail.com', '$2y$10$Yg3H1QPn52/LjSjENs4K9eyHNxP3c2OWYuEujz.MjJjNRs09SxD22', 'usuario', NULL, 1, '2026-06-26 23:44:12', '2026-06-26 23:44:12', NULL),
(107, 'Jorge', 'panajachell45@gmail.com', '$2y$10$584KHxHbUsH7beY7kWDgh.8pWdTM8RYSrkfCOUXj4COgE3Vevxynq', 'usuario', NULL, 1, '2026-06-27 23:10:13', '2026-06-27 23:10:13', NULL),
(108, 'Zam', 'filorodo@hotmail.com', '$2y$10$RN9GXrnwC5AofrqqHXfl7.d9VZPm6oyQ3Y436qVeF7G29jVJOIFqi', 'usuario', NULL, 1, '2026-06-28 09:25:33', '2026-06-28 09:25:33', NULL),
(109, 'Manuel  González', 'josue.gonzalezc@outlook.com', '$2y$10$BDdoZ9JYGgGlWj0EAkshb.Pld5hiUt9I4VD7.9X2B8oo4Yg4Rq5we', 'usuario', NULL, 1, '2026-06-29 17:52:29', '2026-06-29 17:52:29', NULL),
(110, 'Aimme g Meléndez', 'sm8256094@gmail.com', '$2y$10$cii.oqxxev8POG/gI4yO2u.j0AuNJT2ikV.MxrI163ugw4Q.nAywG', 'spicy', NULL, 1, '2026-06-29 18:02:26', '2026-06-29 18:52:35', '2026-07-29 18:52:35'),
(111, 'Mario', 'mariooacabal1234@gmail.com', '$2y$10$yLg3cXW.qMAn9T5iGxK.i.i/1VPNXVWQDvoZj7UYMtx4SEReIjeP6', 'usuario', NULL, 1, '2026-07-01 17:01:53', '2026-07-01 17:01:53', NULL),
(112, 'Jorge', 'jorge42padilla45@gmail.com', '$2y$10$Vi5d1gV6osSPgq4kgKGVkevE8GfWCSCseWJjWAU8AmP5I3Y4ZH4h6', 'usuario', NULL, 1, '2026-07-01 18:15:38', '2026-07-01 18:15:38', NULL),
(113, 'pokero', 'pokero321@gmail.com', '$2y$10$HJfPFNWTo8IuDerHhNIeWuwdQTa88D4P./AI0LI4vJE.S9aNR3IhO', 'usuario', NULL, 1, '2026-07-03 16:28:57', '2026-07-03 16:28:57', NULL),
(114, 'Axel Rivas', 'rivasaxel753@gmail.com', '$2y$10$5.6i7fTdgCvm4dCn.B4IO.Y.poJsgOLXn0IIroiR6LZNqGQ08KkM.', 'usuario', NULL, 1, '2026-07-03 21:27:03', '2026-07-03 21:27:03', NULL),
(115, 'Ionel Lemnaru', 'lemnaru1960@gmail.com', '$2y$10$UbpaHKBxfRVUV8hg5OjHTOmuVm/OKhzwVwnBLkshxyg/iXoXvM7GK', 'usuario', NULL, 1, '2026-07-04 02:32:46', '2026-07-04 02:32:46', NULL),
(116, 'Leonardo Ruiz', 'leonardoruizv@gmail.com', '$2y$10$29YPoKjUkvtXcUazaQOE.u2QfVuRjMmcLBxsxocaIKxy3ZBXDidiK', 'usuario', NULL, 1, '2026-07-04 17:16:28', '2026-07-04 17:16:28', NULL),
(117, 'CARLOS SERNA RAMIREZ', 'carlossernaramirez55@gmail.com', '$2y$10$nil/rZ.OAxyczUqCWi8uuuXVM8re2D3ceq1i7lNNO1a3wUz1Yowx6', 'usuario', NULL, 1, '2026-07-04 22:33:15', '2026-07-04 22:33:15', NULL);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

-- ============================================================
-- canal_guardados
-- ============================================================
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `canal_guardados`;
CREATE TABLE `canal_guardados` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fuente_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `canal_guardados` (`id`, `user_id`, `fuente_id`, `created_at`) VALUES
(1, 1, 70, '2026-05-01 05:29:59'),
(2, 7, 5, '2026-05-01 18:13:59'),
(3, 7, 42, '2026-05-02 18:21:54'),
(4, 7, 32, '2026-05-02 21:32:35'),
(5, 7, 16, '2026-05-02 21:46:20'),
(6, 7, 17, '2026-05-02 21:46:39'),
(7, 7, 70, '2026-05-03 02:24:28'),
(8, 7, 72, '2026-05-03 02:25:23'),
(9, 7, 31, '2026-05-03 02:26:22'),
(10, 1, 33, '2026-05-09 22:10:54'),
(11, 7, 33, '2026-05-10 01:46:38'),
(12, 7, 88, '2026-05-10 02:14:36'),
(13, 7, 107, '2026-05-10 14:43:42'),
(14, 7, 105, '2026-05-10 15:40:09'),
(15, 29, 32, '2026-05-16 08:31:08'),
(33, 5, 62, '2026-05-24 15:48:22'),
(34, 5, 63, '2026-05-24 15:48:28'),
(46, 5, 17, '2026-05-24 15:56:10'),
(48, 5, 31, '2026-05-24 15:56:25'),
(50, 5, 81, '2026-05-24 15:56:37'),
(52, 5, 89, '2026-05-27 18:37:20'),
(53, 5, 68, '2026-05-27 18:37:39'),
(54, 5, 85, '2026-05-27 18:38:10'),
(55, 5, 76, '2026-05-27 18:38:25'),
(56, 5, 30, '2026-05-27 18:38:46'),
(57, 5, 29, '2026-05-27 18:38:58'),
(58, 5, 61, '2026-05-27 18:39:14'),
(59, 5, 6, '2026-05-27 18:41:39'),
(61, 31, 61, '2026-05-30 11:33:39'),
(62, 5, 15, '2026-06-05 09:38:25'),
(63, 5, 66, '2026-06-05 09:38:50'),
(64, 79, 101, '2026-06-14 19:03:59'),
(65, 7, 132, '2026-06-15 01:17:33'),
(66, 7, 101, '2026-06-15 14:28:32'),
(67, 7, 127, '2026-06-15 18:23:54'),
(68, 77, 101, '2026-06-15 21:37:45'),
(69, 77, 126, '2026-06-15 21:38:03'),
(71, 80, 142, '2026-06-17 00:29:35'),
(72, 87, 88, '2026-06-17 21:23:22'),
(73, 7, 126, '2026-06-17 22:35:03'),
(74, 80, 70, '2026-06-19 00:24:16'),
(75, 77, 61, '2026-06-22 19:35:06'),
(76, 77, 150, '2026-06-23 00:26:13'),
(77, 104, 126, '2026-06-25 01:56:45'),
(78, 104, 101, '2026-06-25 01:57:00'),
(82, 77, 127, '2026-06-26 03:00:18'),
(83, 5, 105, '2026-06-27 11:05:39'),
(84, 5, 115, '2026-06-27 11:06:05'),
(85, 5, 150, '2026-06-27 11:06:30'),
(86, 5, 710, '2026-06-27 11:06:50'),
(87, 5, 119, '2026-06-28 10:43:47'),
(88, 5, 94, '2026-06-28 10:48:33'),
(89, 5, 83, '2026-06-28 10:49:10'),
(90, 5, 111, '2026-06-28 10:50:51'),
(91, 5, 5, '2026-06-28 10:53:05'),
(92, 112, 135, '2026-07-01 18:39:58'),
(93, 112, 134, '2026-07-01 18:48:49'),
(94, 112, 107, '2026-07-01 19:20:56'),
(95, 112, 31, '2026-07-01 19:24:56'),
(96, 112, 477, '2026-07-01 19:35:36'),
(97, 80, 73, '2026-07-01 23:34:16'),
(98, 80, 17, '2026-07-01 23:50:59'),
(99, 80, 7, '2026-07-01 23:58:28'),
(100, 117, 101, '2026-07-04 23:26:30'),
(101, 117, 123, '2026-07-04 23:27:18'),
(102, 117, 134, '2026-07-04 23:28:12');

ALTER TABLE `canal_guardados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_save` (`user_id`,`fuente_id`);
ALTER TABLE `canal_guardados`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

-- ============================================================
-- canal_likes
-- ============================================================
DROP TABLE IF EXISTS `canal_likes`;
CREATE TABLE `canal_likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fuente_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `canal_likes` (`id`, `user_id`, `fuente_id`, `created_at`) VALUES
(1, 1, 70, '2026-05-01 05:29:57'),
(2, 1, 31, '2026-05-01 20:13:56'),
(3, 1, 113, '2026-05-09 21:09:17'),
(4, 1, 33, '2026-05-09 22:10:53'),
(6, 31, 51, '2026-05-27 04:01:47'),
(7, 31, 61, '2026-05-27 12:51:51'),
(8, 79, 101, '2026-06-14 19:55:34'),
(9, 87, 88, '2026-06-17 21:23:24'),
(10, 90, 32, '2026-06-18 20:04:32'),
(11, 99, 33, '2026-06-23 00:42:31'),
(12, 106, 127, '2026-06-26 23:44:30'),
(13, 109, 32, '2026-06-30 15:53:56'),
(14, 109, 31, '2026-06-30 15:55:22'),
(15, 106, 101, '2026-07-01 16:05:56'),
(16, 112, 135, '2026-07-01 18:39:55'),
(17, 112, 31, '2026-07-01 19:24:57'),
(18, 112, 477, '2026-07-01 19:35:36'),
(19, 109, 89, '2026-07-03 16:20:48');

ALTER TABLE `canal_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_like` (`user_id`,`fuente_id`);
ALTER TABLE `canal_likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

-- ============================================================
-- canal_reportes
-- ============================================================
DROP TABLE IF EXISTS `canal_reportes`;
CREATE TABLE `canal_reportes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fuente_id` int(10) UNSIGNED NOT NULL,
  `comentario` text DEFAULT NULL,
  `dispositivo` varchar(500) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `canal_reportes` (`id`, `user_id`, `fuente_id`, `comentario`, `dispositivo`, `pais`, `ip`, `created_at`) VALUES
(1, 1, 28, 'El canal no funciona!!!', 'Escritorio', 'Honduras', '190.242.26.173', '2026-05-01 06:19:54'),
(2, 7, 83, 'no funciona', 'Escritorio', 'Argentina', '190.183.253.147', '2026-05-02 18:18:40'),
(3, 17, 67, 'no se ve', 'Escritorio', 'Mexico', '2806:262:480:9104:d2c:88cd:bafb:7c6', '2026-05-09 00:08:22'),
(4, 17, 84, 'no se ve', 'Escritorio', 'Mexico', '2806:262:480:9104:d2c:88cd:bafb:7c6', '2026-05-09 00:09:22'),
(5, 17, 96, 'solo se queda cargando o se debe ver con VPN ?', 'Escritorio', 'Argentina', '206.223.232.117', '2026-05-12 19:50:07'),
(6, 17, 96, 'no se ve', 'Escritorio', 'United States', '86.62.28.161', '2026-06-07 14:14:03'),
(7, 39, 62, 'No carga el canal', 'Móvil', 'El Salvador', '190.87.170.166', '2026-06-07 16:52:41'),
(8, 87, 88, 'No termina de cargar el canal, funciona bien los otros menos Telemundo', 'Móvil', 'Honduras', '181.115.120.143', '2026-06-17 16:53:45'),
(9, 7, 101, 'NO FUNCONA DSPORTS!!', 'Escritorio', 'Argentina', '138.118.114.207', '2026-06-19 22:15:59'),
(10, 94, 61, 'No carga, no se reproduce', 'Escritorio', 'Argentina', '190.103.176.150', '2026-06-20 11:06:07'),
(11, 94, 61, 'No carga', 'Móvil', 'Argentina', '190.103.176.150', '2026-06-20 12:10:25'),
(12, 77, 78, 'no se reproduce error', 'Escritorio', 'Colombia', '181.61.208.205', '2026-06-22 19:36:28'),
(13, 77, 67, 'no carga se queda en negro', 'Escritorio', 'Colombia', '181.61.208.205', '2026-06-22 19:36:55'),
(14, 77, 61, 'no carga se queda en espera', 'Escritorio', 'Colombia', '181.61.208.205', '2026-06-22 19:37:29'),
(15, 77, 61, 'no carga y dice error al cargar el stream', 'Escritorio', 'Colombia', '181.61.208.205', '2026-07-02 03:10:34');

ALTER TABLE `canal_reportes` ADD PRIMARY KEY (`id`);
ALTER TABLE `canal_reportes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- ============================================================
-- canal_autodesactivados (sin filas en el dump)
-- ============================================================
DROP TABLE IF EXISTS `canal_autodesactivados`;
CREATE TABLE `canal_autodesactivados` (
  `id` int(10) UNSIGNED NOT NULL,
  `fuente_id` int(10) UNSIGNED NOT NULL,
  `total_reportes` int(11) NOT NULL,
  `razon` varchar(500) DEFAULT NULL,
  `desactivado_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `canal_autodesactivados` ADD PRIMARY KEY (`id`);
ALTER TABLE `canal_autodesactivados` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- ============================================================
-- donaciones
-- ============================================================
DROP TABLE IF EXISTS `donaciones`;
CREATE TABLE `donaciones` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cafes` int(11) NOT NULL DEFAULT 1,
  `meses` int(11) NOT NULL DEFAULT 1,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donaciones` (`id`, `user_id`, `cafes`, `meses`, `notas`, `created_at`) VALUES
(1, 70, 1, 1, NULL, '2026-06-11 17:39:44'),
(2, 75, 1, 1, 'Usuario cortesía', '2026-06-15 18:00:29'),
(3, 86, 1, 1, NULL, '2026-06-17 03:15:45'),
(4, 85, 1, 1, 'Cortesía', '2026-06-17 14:54:14'),
(5, 110, 1, 1, 'Cortesia', '2026-06-29 18:52:35');

ALTER TABLE `donaciones` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
ALTER TABLE `donaciones` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- ============================================================
-- eu_access
-- ============================================================
DROP TABLE IF EXISTS `eu_access`;
CREATE TABLE `eu_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `pais` varchar(100) NOT NULL DEFAULT '',
  `estado` enum('pendiente','aprobado','denegado') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `eu_access` (`id`, `user_id`, `ip`, `pais`, `estado`, `created_at`, `updated_at`) VALUES
(1, 34, '104.28.161.250', 'Spain', 'pendiente', '2026-06-06 19:26:56', '2026-06-06 19:26:56'),
(2, 35, '95.16.140.214', 'Spain', 'pendiente', '2026-06-06 19:37:09', '2026-06-06 19:37:09'),
(3, 36, '188.79.74.218', 'Spain', 'pendiente', '2026-06-07 10:04:05', '2026-06-07 10:04:05'),
(4, 37, '90.169.34.45', 'Spain', 'pendiente', '2026-06-07 13:11:40', '2026-06-07 13:11:40'),
(5, 38, '85.85.31.161', 'Spain', 'pendiente', '2026-06-07 16:05:27', '2026-06-07 16:05:27'),
(6, 40, '67.218.227.246', 'Spain', 'pendiente', '2026-06-07 17:04:39', '2026-06-07 17:04:39'),
(7, 41, '84.78.245.44', 'Spain', 'pendiente', '2026-06-07 19:10:33', '2026-06-07 19:10:33'),
(8, 42, '66.81.187.182', 'Spain', 'pendiente', '2026-06-07 19:13:13', '2026-06-07 19:13:13'),
(9, 43, '104.28.162.28', 'Spain', 'pendiente', '2026-06-07 19:34:12', '2026-06-07 19:34:12'),
(10, 44, '104.28.162.223', 'Spain', 'pendiente', '2026-06-08 20:04:37', '2026-06-08 20:04:37'),
(11, 45, '31.221.150.162', 'Spain', 'pendiente', '2026-06-08 22:01:38', '2026-06-08 22:01:38'),
(12, 47, '185.165.243.145', 'Netherlands', 'pendiente', '2026-06-09 10:00:01', '2026-06-09 10:00:01'),
(13, 48, '85.86.27.240', 'Spain', 'pendiente', '2026-06-09 22:27:33', '2026-06-09 22:27:33'),
(14, 49, '46.37.82.161', 'Spain', 'pendiente', '2026-06-10 01:08:09', '2026-06-10 01:08:09'),
(15, 50, '90.166.19.33', 'Spain', 'pendiente', '2026-06-10 19:04:08', '2026-06-10 19:04:08'),
(16, 51, '162.120.249.113', 'Spain', 'pendiente', '2026-06-10 19:11:20', '2026-06-10 19:11:20'),
(17, 52, '90.173.164.43', 'Spain', 'pendiente', '2026-06-10 19:16:09', '2026-06-10 19:16:09'),
(18, 53, '85.87.102.158', 'Spain', 'pendiente', '2026-06-10 19:18:34', '2026-06-10 19:18:34'),
(19, 54, '91.245.201.208', 'Spain', 'pendiente', '2026-06-10 19:20:41', '2026-06-10 19:20:41'),
(20, 55, '212.85.246.183', 'Spain', 'pendiente', '2026-06-10 19:28:29', '2026-06-10 19:28:29'),
(21, 56, '104.28.161.253', 'Spain', 'pendiente', '2026-06-10 19:37:50', '2026-06-10 19:37:50'),
(22, 57, '78.30.8.58', 'Spain', 'pendiente', '2026-06-10 19:41:00', '2026-06-10 19:41:00'),
(23, 58, '92.185.116.41', 'Spain', 'pendiente', '2026-06-10 19:47:36', '2026-06-10 19:47:36'),
(24, 59, '46.222.154.73', 'Spain', 'pendiente', '2026-06-10 19:49:38', '2026-06-10 19:49:38'),
(25, 60, '85.85.78.9', 'Spain', 'pendiente', '2026-06-10 19:55:33', '2026-06-10 19:55:33'),
(26, 61, '172.226.116.49', 'Spain', 'pendiente', '2026-06-10 20:12:03', '2026-06-10 20:12:03'),
(27, 62, '104.28.163.69', 'Spain', 'pendiente', '2026-06-10 20:42:37', '2026-06-10 20:42:37'),
(28, 23, '104.28.161.253', 'Spain', 'pendiente', '2026-06-10 20:55:26', '2026-06-10 20:55:26'),
(29, 63, '51.15.19.231', 'Netherlands', 'pendiente', '2026-06-10 20:58:23', '2026-06-10 20:58:23'),
(30, 64, '79.72.55.116', 'Spain', 'pendiente', '2026-06-10 21:05:06', '2026-06-10 21:05:06'),
(31, 65, '104.28.162.26', 'Spain', 'pendiente', '2026-06-11 00:15:41', '2026-06-11 00:15:41'),
(32, 66, '151.248.23.205', 'Spain', 'pendiente', '2026-06-11 09:19:45', '2026-06-11 09:19:45'),
(33, 67, '213.195.80.158', 'Spain', 'pendiente', '2026-06-11 12:46:54', '2026-06-11 12:46:54'),
(34, 71, '86.252.4.19', 'France', 'pendiente', '2026-06-11 17:51:44', '2026-06-11 17:51:44'),
(35, 73, '62.42.89.99', 'Spain', 'pendiente', '2026-06-11 18:53:08', '2026-06-11 18:53:08');

ALTER TABLE `eu_access` ADD PRIMARY KEY (`id`), ADD KEY `idx_ip` (`ip`), ADD KEY `idx_user` (`user_id`), ADD KEY `idx_estado` (`estado`);
ALTER TABLE `eu_access` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

-- ============================================================
-- password_resets
-- ============================================================
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_resets` (`id`, `usuario_id`, `token_hash`, `expira_en`, `usado`, `ip`, `creado_en`) VALUES
(1, 80, 'c46023d65133ad630e3a78c6773712f09ec9eae306edf2ea3e83fc23e2b60b40', '2026-06-30 18:24:37', 1, '189.201.8.102', '2026-06-30 19:24:37'),
(2, 80, 'f123a79a65ba0974afcb1ad8201f4b1deab1739a96585ee66655cce223cd78b3', '2026-06-30 18:26:11', 0, '189.201.8.102', '2026-06-30 19:26:11');

ALTER TABLE `password_resets` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uq_token` (`token_hash`), ADD KEY `idx_usuario` (`usuario_id`);
ALTER TABLE `password_resets` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- ============================================================
-- tv_login_tokens (sin filas en el dump)
-- ============================================================
DROP TABLE IF EXISTS `tv_login_tokens`;
CREATE TABLE `tv_login_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `token` char(32) NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `tv_login_tokens` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uq_token` (`token`);
ALTER TABLE `tv_login_tokens` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

-- ============================================================
-- chat_messages
-- ============================================================
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `canal_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_name` varchar(100) NOT NULL,
  `user_rol` enum('admin','spicy','usuario') NOT NULL DEFAULT 'usuario',
  `message` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chat_messages` (`id`, `canal_id`, `user_id`, `user_name`, `user_rol`, `message`, `created_at`) VALUES
(1, 0, 1, 'Alex', 'admin', '¡Bienvenido a Tele Deportes! El chat se ha desarrollado y está en BETAv1. Debes registrarte en la web para utilizarlo. Repórtanos cualquier inconveniente utilizando el botón "Reportar" en cada canal.', '2026-05-08 04:30:56'),
(3, 0, 7, 'frodo', 'usuario', 'No funcionan tudn', '2026-05-09 11:58:19'),
(4, 0, 7, 'frodo', 'usuario', 'Pueden poner Opciónes de canalss de Argentina? Y poner los partidos de Argentina', '2026-05-09 12:19:45'),
(5, 0, 1, 'Tele Deportes', 'admin', 'Se agregaron los partidos de la liga Argentina. En unos minutos se agregarán los canales correspondientes.', '2026-05-09 17:56:20'),
(6, 0, 1, 'Tele Deportes', 'admin', '⚽⚽⚽', '2026-05-09 18:35:23'),
(7, 0, 17, 'fer', 'usuario', 'Pueden añadir TUDN USA , por favor ?', '2026-05-09 18:38:06'),
(8, 0, 1, 'Tele Deportes', 'admin', 'TUDN USA: https://teledeportes.top/?p=canal&id=70', '2026-05-09 18:48:45'),
(9, 0, 7, 'frodo', 'usuario', 'La MLS?', '2026-05-09 19:23:51'),
(10, 0, 7, 'frodo', 'usuario', 'Otro pregunta porque no ponen opciones Argentina en lps partidos o DISNEY,?', '2026-05-09 19:24:41'),
(11, 0, 7, 'frodo', 'usuario', 'POR EJEMPLO JUVE VS LECCE ESPN 2 ARG', '2026-05-09 19:24:58'),
(12, 0, 1, 'Tele Deportes', 'admin', 'Se agregarán los juegos de la MLS. En el caso de las señales de Disney dejaron de ser estables, por eso las dejamos de utilizar.', '2026-05-09 19:33:25'),
(13, 0, 1, 'Tele Deportes', 'admin', 'Progresivamente se agregarán más ligas y deportes a la página. Todavía se están puliendo algunos detalles técnicos para que funciona perfectamente.', '2026-05-09 19:53:30'),
(14, 0, 1, 'Tele Deportes', 'admin', 'Los partidos de MLS ya están agregados. Se agregarán los canales correspondientes.', '2026-05-09 20:00:00'),
(15, 0, 22, 'Daniel Garcia Brito', 'usuario', 'aqui se ve la ufc no?', '2026-05-10 01:14:33'),
(16, 0, 7, 'frodo', 'usuario', 'Chivas VS tigres??', '2026-05-10 01:53:05'),
(17, 0, 2, 'Tele Deportes', 'admin', 'En el canal Telemundo puedes ver ese partido: https://teledeportes.top/?p=canal&id=88', '2026-05-10 01:57:10'),
(18, 0, 7, 'frodo', 'usuario', 'Puede arreglar TUDN y poner cual es MX y USA?', '2026-05-10 03:06:32'),
(19, 0, 17, 'fer', 'usuario', 'amigo podrian poner en calendario los juegos de liga mx ?', '2026-05-10 13:56:00');

ALTER TABLE `chat_messages` ADD PRIMARY KEY (`id`), ADD KEY `idx_canal_id` (`canal_id`,`id`);
ALTER TABLE `chat_messages` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

SET FOREIGN_KEY_CHECKS=1;
