<?php
namespace SaQle\Commons;
trait FileUtils{
	 static $image_mime_types = array( 
	 'image/vnd.dxf'=>array('name'=>'AutoCAD DXF', 'extension'=>'.dxf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/bmp'=>array('name'=>'Bitmap Image File', 'extension'=>'.bmp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/prs.btif'=>array('name'=>'BTIF', 'extension'=>'.btif', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.dvb.subtitle'=>array('name'=>'Close Captioning - Subtitle', 'extension'=>'.sub', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-cmu-raster'=>array('name'=>'CMU Image', 'extension'=>'.ras', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/cgm'=>array('name'=>'Computer Graphics Metafile', 'extension'=>'.cgm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-cmx'=>array('name'=>'Corel Metafile Exchange (CMX)', 'extension'=>'.cmx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.dece.graphic'=>array('name'=>'DECE Graphic', 'extension'=>'.uvi', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.djvu'=>array('name'=>'DjVu', 'extension'=>'.djvu', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.dwg'=>array('name'=>'DWG Drawing', 'extension'=>'.dwg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.fujixerox.edmics-mmr'=>array('name'=>'EDMICS 2000', 'extension'=>'.mmr', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.fujixerox.edmics-rlc'=>array('name'=>'EDMICS 2000', 'extension'=>'.rlc', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.xiff'=>array('name'=>'eXtended Image File Format (XIFF)', 'extension'=>'.xif', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.fst'=>array('name'=>'FAST Search &amp; Transfer ASA', 'extension'=>'.fst', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.fastbidsheet'=>array('name'=>'FastBid Sheet', 'extension'=>'.fbs', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.fpx'=>array('name'=>'FlashPix', 'extension'=>'.fpx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.net-fpx'=>array('name'=>'FlashPix', 'extension'=>'.npx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-freehand'=>array('name'=>'FreeHand MX', 'extension'=>'.fh', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/g3fax'=>array('name'=>'G3 Fax Image', 'extension'=>'.g3', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/gif'=>array('name'=>'Graphics Interchange Format', 'extension'=>'.gif', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-icon'=>array('name'=>'Icon Image', 'extension'=>'.ico', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/ief'=>array('name'=>'Image Exchange Format', 'extension'=>'.ief', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/jpeg'=>array('name'=>'JPEG Image', 'extension'=>'.jpeg, .jpg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-citrix-jpeg'=>array('name'=>'JPEG Image (Citrix client)', 'extension'=>'.jpeg, .jpg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/pjpeg'=>array('name'=>'JPEG Image (Progressive)', 'extension'=>'.pjpeg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.ms-modi'=>array('name'=>'Microsoft Document Imaging Format', 'extension'=>'.mdi', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/ktx'=>array('name'=>'OpenGL Textures (KTX)', 'extension'=>'.ktx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-pcx'=>array('name'=>'PCX Image', 'extension'=>'.pcx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.adobe.photoshop'=>array('name'=>'Photoshop Document', 'extension'=>'.psd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-pict'=>array('name'=>'PICT Image', 'extension'=>'.pic', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-portable-anymap'=>array('name'=>'Portable Anymap Image', 'extension'=>'.pnm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-portable-bitmap'=>array('name'=>'Portable Bitmap Format', 'extension'=>'.pbm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-portable-graymap'=>array('name'=>'Portable Graymap Format', 'extension'=>'.pgm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/png'=>array('name'=>'Portable Network Graphics (PNG)', 'extension'=>'.png', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-citrix-png'=>array('name'=>'Portable Network Graphics (PNG) (Citrix client)', 'extension'=>'.png', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-png'=>array('name'=>'Portable Network Graphics (PNG) (x-token)', 'extension'=>'.png', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-portable-pixmap'=>array('name'=>'Portable Pixmap Format', 'extension'=>'.ppm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/svg+xml'=>array('name'=>'Scalable Vector Graphics (SVG)', 'extension'=>'.svg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-rgb'=>array('name'=>'Silicon Graphics RGB Bitmap', 'extension'=>'.rgb', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/tiff'=>array('name'=>'Tagged Image File Format', 'extension'=>'.tiff', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/vnd.wap.wbmp'=>array('name'=>'WAP Bitamp (WBMP)', 'extension'=>'.wbmp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/webp'=>array('name'=>'WebP Image', 'extension'=>'.webp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-xbitmap'=>array('name'=>'X BitMap', 'extension'=>'.xbm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-xpixmap'=>array('name'=>'X PixMap', 'extension'=>'.xpm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 'image/x-xwindowdump'=>array('name'=>'X Window Dump', 'extension'=>'.xwd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/imagefileicon.png'),
	 );
	 
	 static $video_mime_types = array(
	 'video/3gpp'=>array('name'=>'3GP', 'extension'=>'.3gp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/3gpp2'=>array('name'=>'3GP2', 'extension'=>'.3g2', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-msvideo'=>array('name'=>'Audio Video Interleave (AVI)', 'extension'=>'.avi', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.dece.hd'=>array('name'=>'DECE High Definition Video', 'extension'=>'.uvh', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.dece.mobile'=>array('name'=>'DECE Mobile Video', 'extension'=>'.uvm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.uvvu.mp4'=>array('name'=>'DECE MP4', 'extension'=>'.uvu', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.dece.pd'=>array('name'=>'DECE PD Video', 'extension'=>'.uvp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.dece.sd'=>array('name'=>'DECE SD Video', 'extension'=>'.uvs', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.dece.video'=>array('name'=>'DECE Video', 'extension'=>'.uvv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.fvt'=>array('name'=>'FAST Search &amp; Transfer ASA', 'extension'=>'.fvt', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-f4v'=>array('name'=>'Flash Video', 'extension'=>'.f4v', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-flv'=>array('name'=>'Flash Video', 'extension'=>'.flv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-fli'=>array('name'=>'FLI/FLC Animation Format', 'extension'=>'.fli', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/h261'=>array('name'=>'H.261', 'extension'=>'.h261', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/h263'=>array('name'=>'H.263', 'extension'=>'.h263', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/h264'=>array('name'=>'H.264', 'extension'=>'.h264', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/jpm'=>array('name'=>'JPEG 2000 Compound Image File Format', 'extension'=>'.jpm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/jpeg'=>array('name'=>'JPGVideo', 'extension'=>'.jpgv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-m4v'=>array('name'=>'M4v', 'extension'=>'.m4v', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-ms-asf'=>array('name'=>'Microsoft Advanced Systems Format (ASF)', 'extension'=>'.asf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.ms-playready.media.pyv'=>array('name'=>'Microsoft PlayReady Ecosystem Video', 'extension'=>'.pyv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-ms-wm'=>array('name'=>'Microsoft Windows Media', 'extension'=>'.wm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-ms-wmx'=>array('name'=>'Microsoft Windows Media Audio/Video Playlist', 'extension'=>'.wmx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-ms-wmv'=>array('name'=>'Microsoft Windows Media Video', 'extension'=>'.wmv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-ms-wvx'=>array('name'=>'Microsoft Windows Media Video Playlist', 'extension'=>'.wvx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/mj2'=>array('name'=>'Motion JPEG 2000', 'extension'=>'.mj2', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.mpegurl'=>array('name'=>'MPEG Url', 'extension'=>'.mxu', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/mpeg'=>array('name'=>'MPEG Video', 'extension'=>'.mpeg', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/mp4'=>array('name'=>'MPEG-4 Video', 'extension'=>'.mp4', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/ogg'=>array('name'=>'Ogg Video', 'extension'=>'.ogv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/webm'=>array('name'=>'Open Web Media Project - Video', 'extension'=>'.webm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/quicktime'=>array('name'=>'Quicktime Video', 'extension'=>'.qt', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/x-sgi-movie'=>array('name'=>'SGI Movie', 'extension'=>'.movie', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 'video/vnd.vivo'=>array('name'=>'Vivo', 'extension'=>'.viv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/videofileicon.png'),
	 );
	 
	 static $audio_mime_types = array(
	 'audio/adpcm'=>array('name'=>'Adaptive differential pulse-code modulation', 'extension'=>'.adp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-aac'=>array('name'=>'Advanced Audio Coding (AAC)', 'extension'=>'.aac', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-aiff'=>array('name'=>'Audio Interchange File Format', 'extension'=>'.aif', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.dece.audio'=>array('name'=>'DECE Audio', 'extension'=>'.uva', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.digital-winds'=>array('name'=>'Digital Winds Music', 'extension'=>'.eol', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.dra'=>array('name'=>'DRA Audio', 'extension'=>'.dra', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.dts'=>array('name'=>'DTS Audio', 'extension'=>'.dts', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.dts.hd'=>array('name'=>'DTS High Definition Audio', 'extension'=>'.dtshd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.rip'=>array('name'=>'Hit\'n\'Mix', 'extension'=>'.rip', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.lucent.voice'=>array('name'=>'Lucent Voice', 'extension'=>'.lvp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-mpegurl'=>array('name'=>'M3U (Multimedia Playlist)', 'extension'=>'.m3u', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.ms-playready.media.pya'=>array('name'=>'Microsoft PlayReady Ecosystem', 'extension'=>'.pya', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-ms-wma'=>array('name'=>'Microsoft Windows Media Audio', 'extension'=>'.wma', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-ms-wax'=>array('name'=>'Microsoft Windows Media Audio Redirector', 'extension'=>'.wax', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/midi'=>array('name'=>'MIDI - Musical Instrument Digital Interface', 'extension'=>'.mid', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/mpeg'=>array('name'=>'MPEG Audio', 'extension'=>'.mpga', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/mp4'=>array('name'=>'MPEG-4 Audio', 'extension'=>'.mp4a', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.nuera.ecelp4800'=>array('name'=>'Nuera ECELP 4800', 'extension'=>'.ecelp4800', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.nuera.ecelp7470'=>array('name'=>'Nuera ECELP 7470', 'extension'=>'.ecelp7470', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/vnd.nuera.ecelp9600'=>array('name'=>'Nuera ECELP 9600', 'extension'=>'.ecelp9600', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/ogg'=>array('name'=>'Ogg Audio', 'extension'=>'.oga', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/webm'=>array('name'=>'Open Web Media Project - Audio', 'extension'=>'.weba', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-pn-realaudio'=>array('name'=>'Real Audio Sound', 'extension'=>'.ram', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-pn-realaudio-plugin'=>array('name'=>'Real Audio Sound', 'extension'=>'.rmp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/basic'=>array('name'=>'Sun Audio - Au file format', 'extension'=>'.au', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 'audio/x-wav'=>array('name'=>'Waveform Audio File Format (WAV)', 'extension'=>'.wav', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/audiofileicon.png'),
	 );
	 
	 static $text_mime_types = array(
	 'text/x-asm'=>array('name'=>'Assembler Source File', 'extension'=>'.s', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/plain-bas'=>array('name'=>'BAS Partitur Format', 'extension'=>'.par', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-c'=>array('name'=>'C Source File', 'extension'=>'.c', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/css'=>array('name'=>'Cascading Style Sheets (CSS)', 'extension'=>'.css', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/csv'=>array('name'=>'Comma-Seperated Values', 'extension'=>'.csv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.curl'=>array('name'=>'Curl - Applet', 'extension'=>'.curl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.curl.dcurl'=>array('name'=>'Curl - Detached Applet', 'extension'=>'.dcurl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.curl.mcurl'=>array('name'=>'Curl - Manifest File', 'extension'=>'.mcurl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.curl.scurl'=>array('name'=>'Curl - Source Code', 'extension'=>'.scurl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.fmi.flexstor'=>array('name'=>'FLEXSTOR', 'extension'=>'.flx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-fortran'=>array('name'=>'Fortran Source File', 'extension'=>'.f', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.graphviz'=>array('name'=>'Graphviz', 'extension'=>'.gv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/html'=>array('name'=>'HyperText Markup Language (HTML)', 'extension'=>'.html', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/calendar'=>array('name'=>'iCalendar', 'extension'=>'.ics', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.in3d.3dml'=>array('name'=>'In3D - 3DML', 'extension'=>'.3dml', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.in3d.spot'=>array('name'=>'In3D - 3DML', 'extension'=>'.spot', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.sun.j2me.app-descriptor'=>array('name'=>'J2ME App Descriptor', 'extension'=>'.jad', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-java-source,java'=>array('name'=>'Java Source File', 'extension'=>'.java', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.fly'=>array('name'=>'mod_fly / fly.cgi', 'extension'=>'.fly', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/n3'=>array('name'=>'Notation3', 'extension'=>'.n3', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-pascal'=>array('name'=>'Pascal Source File', 'extension'=>'.p', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/prs.lines.tag'=>array('name'=>'PRS Lines Tag', 'extension'=>'.dsc', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/richtext'=>array('name'=>'Rich Text Format (RTF)', 'extension'=>'.rtx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-setext'=>array('name'=>'Setext', 'extension'=>'.etx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/sgml'=>array('name'=>'Standard Generalized Markup Language (SGML)', 'extension'=>'.sgml', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/tab-separated-values'=>array('name'=>'Tab Seperated Values', 'extension'=>'.tsv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/plain'=>array('name'=>'Text File', 'extension'=>'.txt', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/troff'=>array('name'=>'troff', 'extension'=>'.t', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/turtle'=>array('name'=>'Turtle (Terse RDF Triple Language)', 'extension'=>'.ttl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/uri-list'=>array('name'=>'URI Resolution Services', 'extension'=>'.uri', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-uuencode'=>array('name'=>'UUEncode', 'extension'=>'.uu', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-vcalendar'=>array('name'=>'vCalendar', 'extension'=>'.vcs', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/x-vcard'=>array('name'=>'vCard', 'extension'=>'.vcf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.wap.wml'=>array('name'=>'Wireless Markup Language (WML)', 'extension'=>'.wml', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/vnd.wap.wmlscript'=>array('name'=>'Wireless Markup Language Script (WMLScript)', 'extension'=>'.wmls', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'text/yaml'=>array('name'=>'YAML Ain\'t Markup Language / Yet Another Markup Language', 'extension'=>'.yaml', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 );
	 
	 static $document_mime_types = array(
	 'application/x-msaccess'=>array('name'=>'Microsoft Access', 'extension'=>'.mdb', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/x-ms-asf'=>array('name'=>'Microsoft Advanced Systems Format (ASF)', 'extension'=>'.asf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msdownload'=>array('name'=>'Microsoft Application', 'extension'=>'.exe', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-artgalry'=>array('name'=>'Microsoft Artgalry', 'extension'=>'.cil', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-cab-compressed'=>array('name'=>'Microsoft Cabinet File', 'extension'=>'.cab', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-ims'=>array('name'=>'Microsoft Class Server', 'extension'=>'.ims', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-ms-application'=>array('name'=>'Microsoft ClickOnce', 'extension'=>'.application', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msclip'=>array('name'=>'Microsoft Clipboard Clip', 'extension'=>'.clp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'image/vnd.ms-modi'=>array('name'=>'Microsoft Document Imaging Format', 'extension'=>'.mdi', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-fontobject'=>array('name'=>'Microsoft Embedded OpenType', 'extension'=>'.eot', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.ms-excel'=>array('name'=>'Microsoft Excel', 'extension'=>'.xls', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-excel.addin.macroenabled.12'=>array('name'=>'Microsoft Excel - Add-In File', 'extension'=>'.xlam', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-excel.sheet.binary.macroenabled.12'=>array('name'=>'Microsoft Excel - Binary Workbook', 'extension'=>'.xlsb', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-excel.template.macroenabled.12'=>array('name'=>'Microsoft Excel - Macro-Enabled Template File', 'extension'=>'.xltm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-excel.sheet.macroenabled.12'=>array('name'=>'Microsoft Excel - Macro-Enabled Workbook', 'extension'=>'.xlsm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-htmlhelp'=>array('name'=>'Microsoft Html Help File', 'extension'=>'.chm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-mscardfile'=>array('name'=>'Microsoft Information Card', 'extension'=>'.crd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-lrm'=>array('name'=>'Microsoft Learning Resource Module', 'extension'=>'.lrm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msmediaview'=>array('name'=>'Microsoft MediaView', 'extension'=>'.mvb', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msmoney'=>array('name'=>'Microsoft Money', 'extension'=>'.mny', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>array('name'=>'Microsoft Office - OOXML - Presentation', 'extension'=>'.pptx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.presentationml.slide'=>array('name'=>'Microsoft Office - OOXML - Presentation (Slide)', 'extension'=>'.sldx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.presentationml.slideshow'=>array('name'=>'Microsoft Office - OOXML - Presentation (Slideshow)', 'extension'=>'.ppsx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.presentationml.template'=>array('name'=>'Microsoft Office - OOXML - Presentation Template', 'extension'=>'.potx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>array('name'=>'Microsoft Office - OOXML - Spreadsheet', 'extension'=>'.xlsx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.spreadsheetml.template'=>array('name'=>'Microsoft Office - OOXML - Spreadsheet Template', 'extension'=>'.xltx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>array('name'=>'Microsoft Office - OOXML - Word Document', 'extension'=>'.docx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/worddocumenticon.png'),
	 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'=>array('name'=>'Microsoft Office - OOXML - Word Document Template', 'extension'=>'.dotx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msbinder'=>array('name'=>'Microsoft Office Binder', 'extension'=>'.obd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-officetheme'=>array('name'=>'Microsoft Office System Release Theme', 'extension'=>'.thmx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/onenote'=>array('name'=>'Microsoft OneNote', 'extension'=>'.onetoc', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'audio/vnd.ms-playready.media.pya'=>array('name'=>'Microsoft PlayReady Ecosystem', 'extension'=>'.pya', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/vnd.ms-playready.media.pyv'=>array('name'=>'Microsoft PlayReady Ecosystem Video', 'extension'=>'.pyv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.ms-powerpoint'=>array('name'=>'Microsoft PowerPoint', 'extension'=>'.ppt', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-powerpoint.addin.macroenabled.12'=>array('name'=>'Microsoft PowerPoint - Add-in file', 'extension'=>'.ppam', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-powerpoint.slide.macroenabled.12'=>array('name'=>'Microsoft PowerPoint - Macro-Enabled Open XML Slide', 'extension'=>'.sldm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-powerpoint.presentation.macroenabled.12'=>array('name'=>'Microsoft PowerPoint - Macro-Enabled Presentation File', 'extension'=>'.pptm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-powerpoint.slideshow.macroenabled.12'=>array('name'=>'Microsoft PowerPoint - Macro-Enabled Slide Show File', 'extension'=>'.ppsm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-powerpoint.template.macroenabled.12'=>array('name'=>'Microsoft PowerPoint - Macro-Enabled Template File', 'extension'=>'.potm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-project'=>array('name'=>'Microsoft Project', 'extension'=>'.mpp', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/x-mspublisher'=>array('name'=>'Microsoft Publisher', 'extension'=>'.pub', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msschedule'=>array('name'=>'Microsoft Schedule+', 'extension'=>'.scd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-silverlight-app'=>array('name'=>'Microsoft Silverlight', 'extension'=>'.xap', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-pki.stl'=>array('name'=>'Microsoft Trust UI Provider - Certificate Trust Link', 'extension'=>'.stl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-pki.seccat'=>array('name'=>'Microsoft Trust UI Provider - Security Catalog', 'extension'=>'.cat', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.visio'=>array('name'=>'Microsoft Visio', 'extension'=>'.vsd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.visio2013'=>array('name'=>'Microsoft Visio 2013', 'extension'=>'.vsdx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/x-ms-wm'=>array('name'=>'Microsoft Windows Media', 'extension'=>'.wm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'audio/x-ms-wma'=>array('name'=>'Microsoft Windows Media Audio', 'extension'=>'.wma', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'audio/x-ms-wax'=>array('name'=>'Microsoft Windows Media Audio Redirector', 'extension'=>'.wax', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/x-ms-wmx'=>array('name'=>'Microsoft Windows Media Audio/Video Playlist', 'extension'=>'.wmx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-ms-wmd'=>array('name'=>'Microsoft Windows Media Player Download Package', 'extension'=>'.wmd', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-wpl'=>array('name'=>'Microsoft Windows Media Player Playlist', 'extension'=>'.wpl', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-ms-wmz'=>array('name'=>'Microsoft Windows Media Player Skin Package', 'extension'=>'.wmz', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/x-ms-wmv'=>array('name'=>'Microsoft Windows Media Video', 'extension'=>'.wmv', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'video/x-ms-wvx'=>array('name'=>'Microsoft Windows Media Video Playlist', 'extension'=>'.wvx', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msmetafile'=>array('name'=>'Microsoft Windows Metafile', 'extension'=>'.wmf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-msterminal'=>array('name'=>'Microsoft Windows Terminal Services', 'extension'=>'.trm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/msword'=>array('name'=>'Microsoft Word', 'extension'=>'.doc', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-word.document.macroenabled.12'=>array('name'=>'Microsoft Word - Macro-Enabled Document', 'extension'=>'.docm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-word.template.macroenabled.12'=>array('name'=>'Microsoft Word - Macro-Enabled Template', 'extension'=>'.dotm', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/x-mswrite'=>array('name'=>'Microsoft Wordpad', 'extension'=>'.wri', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 'application/vnd.ms-works'=>array('name'=>'Microsoft Works', 'extension'=>'.wps', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/x-ms-xbap'=>array('name'=>'Microsoft XAML Browser Application', 'extension'=>'.xbap', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 //'application/vnd.ms-xpsdocument'=>array('name'=>'Microsoft XML Paper Specification', 'extension'=>'.xps', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/textfileicon.png'),
	 );
	 
	 static $application_mime_types = array(
	     'application/pdf'=>array('name'=>'Adobe Portable Document Format', 'extension'=>'.pdf', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/pdffileicon.png'),
		 'application/javascript'=>array('name'=>'JavaScript', 'extension'=>'.js', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/codefileicon.png'),
		 'application/json'=>array('name'=>'JavaScript Object Notation (JSON)', 'extension'=>'.json', 'icon_url'=>RSC_BASE_URL.'public/SiteAssets/images/layout/icons/codefileicon.png'),
	 );
	 static $image_extensions = array("jpg", "jpeg", "tif", "tiff", "bmp", "gif", "png", "eps", "cr2", "raw", "nef", "orf", "sr2", "webp"); //add more image extensions here.
	 static $video_extensions = array("avi", "flv", "ogv", "wmv", "mov", "mp4"); //add more video extensions here.
	 static $pdf_extensions = array("pdf"); //add more pdf extensions here.
	 static $audio_extensions = array("mp3"); //add more audio extensions here.
	 static $code_extensions = array( "txt", "as", "mx", "ada", "ads", "adb", "asm", "asp", "sh", "bsh", "bat", "cmd", "nt",
	 "ml", "mli", "sml", "thy", "c", "cmake", "cbl", "cbd", "cdb", "cdc", "cob", "litcoffee", "h", "hpp", "hxx", "cpp", 
	 "cxx", "cc", "cs", "css", "d", "diff", "patch", "f", "for", "f90", "f95", "f2k", "hs", "lhs", "las", "html", "htm",
	 "shtml", "shtm", "xhtml", "hta", "ini", "inf", "reg", "url", "iss", "java", "js", "jsp", "lua", "lsp", "lisp", "m",
	 "pas", "inc", "pl", "pm", "plx", "php", "ps", "py", "pyw", "rc", "rb", "rbw", "st", "sql", "tcl", "tex", "vb", "vbs",
	 "v", "sv", "vh", "svh", "vhd", "vhdl", "xml", "xsml", "xsl", "xsd", "kml", "yml"); //add more code file extensions.
	 //return the kind of file given a file name.
	 static public function get_file_type($file_name){
		 $file_type = "unknown";
		 //$file_extension = (isset(pathinfo($file_name)['extension'])) ? strtolower(pathinfo($file_name)['extension']) : "";
		 $temp = explode(".", $file_name);
         $file_extension = strtolower(end($temp));
		 $image_extensions = [];
		 $video_extensions = [];
		 foreach(array_values(self::$image_mime_types) as $type_info){
			 $image_extensions[] = str_replace(".", "", strtolower($type_info['extension']));
		 }
		 foreach(array_values(self::$video_mime_types) as $type_info){
			 $video_extensions[] = str_replace(".", "", strtolower($type_info['extension']));
		 }
		 if(in_array($file_extension, self::$image_extensions)){
			$file_type = "image";
		 }elseif(in_array($file_extension, $video_extensions)){
			$file_type = "video";
		 }elseif(in_array($file_extension, self::$pdf_extensions)){
			$file_type = "pdf";
		 }elseif(in_array($file_extension, self::$audio_extensions)){
			$file_type = "audio";
		 }elseif(in_array($file_extension, self::$code_extensions)){
			$file_type = "code";
		 }
		 return $file_type;
	 }
	 //get the contents of a text file and return as an array.
	 static public function get_text_file_contents($file_path){
		 $file = ['success' => false];
		 $file_handler = self::open_file($file_path, "r");
		 if($file_handler->success){
			 $file['content'] = file($file_path);
			 $file['success'] = true;
			 fclose($file_handler->fh);
		 }
         return (Object)$file;
	 }
	 //open a file and return the file handler.
	 static public function open_file($file_path, $mode){
		 $file_handler = ['success' => false];
		 try{
			 $file_handler['fh'] = fopen($file_path, $mode);
			 $file_handler['success'] = true;
	     }catch(Exception $ex){
			 $file_handler['error'] = $ex;
		 }
		 return (Object)$file_handler;
	 }
     static public function get_file_type_icon($file_name){
		 $mime_type = mime_content_type($file_name);
		 $file_mime_types = array_merge(self::$image_mime_types, self::$video_mime_types, self::$audio_mime_types, self::$text_mime_types, self::$document_mime_types, self::$application_mime_types);
		 if(array_key_exists($mime_type, $file_mime_types)){
			 return $file_mime_types[$mime_type]['icon_url'];
		 }else{
			 return RSC_BASE_URL.'public/SiteAssets/images/layout/icons/fileicon.png';
		 }
	 }
	 //generate a name for a user created file or folder.
	 /*
	     @param int $user_id: the id of the user uploading this file or folder.
		 @param int $upload_id: the database id of this upload.
	     @param string $upload_type: folder or file
		 
	 */
	 static public function generate_user_created_file_name($user_id, $upload_id, $upload_type, $file_name){
		 return ($upload_type == "folder") ? $upload_type."".$upload_id."_".time()."".$user_id : $upload_type."".$upload_id."_".time()."".$user_id.".".pathinfo($file_name)['extension'];
	 }
	 //get all the contents of a folder
	 static public function fetch_folder_contents($folder_name, $folders_to_open = array()){
		 $folders = array();
		 $dh = opendir($folder_name);
		 while($filename = readdir($dh)){
			 if($filename != "." && $filename != ".."){
				 $folder = new \stdClass();
				 $folder->parent_path = $folder_name;
				 $folder->full_path = $folder->parent_path ."/".$filename;
				 $folder->open = (in_array($folder->full_path, $folders_to_open)) ? true : false;
				 $folder->size = round((filesize($folder_name."/".$filename) / 1024), 2) ." kb";
				 $folder->type = (is_dir($folder_name."/".$filename)) ? "dir" : "file";
				 $folder->icon = (is_dir($folder_name."/".$filename)) ? LAYOUT_IMAGE_PATH."/icons/folder_icon3.png" : self::get_file_typeIcon($folder_name."/".$filename);
				 if(is_dir($folder_name."/".$filename)) $folder->folder_name = $filename;
				 if(is_file($folder_name."/".$filename)) $folder->file_name = self::get_shortened_string($filename, 20, true);
				 array_push($folders, $folder);
		         if(is_dir($folder_name."/".$filename)) $folder->folders = self::fetch_folder_contents($folder_name."/".$filename, $folders_to_open);
	         }
		 }
		 return  $folders;
	 }
	 static public function get_folders_to_open($folder_to_open, $top_folder = null){
		 $folders = array($folder_to_open);
		 while(pathinfo($folder_to_open)['dirname'] != $top_folder){
			 array_push($folders, pathinfo($folder_to_open)['dirname']);
			 $folder_to_open = pathinfo($folder_to_open)['dirname'];
		 }
		 return $folders;
	 }
	 //construct a folder tree view.
	 static public function construct_folders_tree_view($folders){
		 $folder_tree_view = "<ul>";
		 foreach($folders as $f){
			 if($f->type == "dir"){
				 $display_path = ( isset($f->upload_info->upload_id) ) ? $f->upload_info->display_path : $f->full_path;
				 $folder_name = $f->display_name;
				 $full_path = $f->full_path;
				 $tree_branch_label = ($f->open) ? "<span class='fa fa-folder-open'></span>&nbsp;<span class='folder_label'>$folder_name</span>" : "<span class='fa fa-folder'></span>&nbsp;<span class='folder_label'>$folder_name</span>";
				 $folder_tree_view .= "<li data-folder_path='$full_path' data-display_path='$display_path'><span class='folder_tree_label'>$tree_branch_label</span>";
				 if( $f->open && count($f->folders) > 0){
				    $folder_tree_view .= self::construct_folders_tree_view($f->folders);
				 }
				 $folder_tree_view .= "</li>";
			 }
		 }
		 $folder_tree_view .= "</ul>";
		 return $folder_tree_view;
	 }
	 /*
	     - given a csv file, this function parses the rows and columns into data objects.
		 @param object $file: the file to parse.
		 @param array $columns: an array of string column names for parsed data.
		 @param boolean $ignore_header: whether to ignore the header columns for the file or not.
	 */
	 public function parse_csv_file($file, $columns, $ignore_header = false, $associative_column_indexes = false, $data_separator = ",", $start = 0, $stop = 1000000){
		 $parsed_data = array();
		 $file_name = $file["tmp_name"];
		 if ($file["size"] > 0){
			 $file_handle = fopen($file_name, "r");
			 $row_counter = 0;
			 $stop = $ignore_header ? $stop + 1 : $stop;
			 while (($row = fgetcsv($file_handle, 10000, $data_separator)) !== FALSE){
				 if($row_counter >= $start && $row_counter < $stop){
					 if($ignore_header == true && $row_counter == 0){
						 $row_counter = $row_counter + 1;
						 continue;
					 }else{
						 $data_object = array();
						 if($associative_column_indexes){
							 foreach($columns as $key => $value){
								 $data_value = $value - 1 <= count($row) - 1 ? $row[$value - 1] : null;
								 $data_object[$key] = $data_value;
							 }
						 }else{
							 for($c = 0; $c < count($columns); $c++){
								 $data_value = $c <= count($row) - 1 ? $row[$c] : null;
								 $data_object[$columns[$c]] = $data_value;
							 }
						 }
						 array_push($parsed_data, $data_object);
					 }
				 }
				 $row_counter = $row_counter + 1;
			 }
			 fclose($file_handle);
		 }
		 return $parsed_data;
	 }
     /*
	     - given a csv file, this function parses the rows and columns into data objects.
		 @param object $file: the file to parse.
		 @param array $columns: an array of string column names for parsed data.
		 @param boolean $ignore_header: whether to ignore the header columns for the file or not.
	 */
	 public function parse_csv_file2($file_path, $columns, $ignore_header = false, $associative_column_indexes = false, $data_separator = ",", $start = 0, $stop = 1000000){
		 $parsed_data = array();
		 $file_handle = fopen($file_path, "r");
		 $row_counter = 0;
		 $stop = $ignore_header ? $stop + 1 : $stop;
		 while (($row = fgetcsv($file_handle, 10000, $data_separator)) !== FALSE){
			 if($row_counter >= $start && $row_counter < $stop){
				 if($ignore_header == true && $row_counter == 0){
					 $row_counter = $row_counter + 1;
					 continue;
				 }else{
					 $data_object = array();
					 if($associative_column_indexes){
						 foreach($columns as $key => $value){
							 $data_value = $value - 1 <= count($row) - 1 ? $row[$value - 1] : null;
							 $data_object[$key] = $data_value;
						 }
					 }else{
						 for($c = 0; $c < count($columns); $c++){
							 $data_value = $c <= count($row) - 1 ? $row[$c] : null;
							 $data_object[$columns[$c]] = $data_value;
						 }
					 }
					 array_push($parsed_data, $data_object);
				 }
			 }
			 $row_counter = $row_counter + 1;
		 }
		 fclose($file_handle);
		 return $parsed_data;
	 }
	 public function export_csv_file(){
		 if(isset($_POST["Export"])){
			 header('Content-Type: text/csv; charset=utf-8');  
			 header('Content-Disposition: attachment; filename=data.csv');  
			 $output = fopen("php://output", "w");  
			 fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
			 $query = "SELECT * from employeeinfo ORDER BY emp_id DESC";  
			 $result = mysqli_query($con, $query);  
			 while($row = mysqli_fetch_assoc($result))  {  
				 fputcsv($output, $row);  
			 }  
			 fclose($output);  
	     }  
	 }

	 /**
	 * Return an array of file paths representing the contents of the target
	 * directory, ordered by date instead of by filename.
	 * 
	 * @param string $path The target directory path
	 * @param bool $reverse Whether to sort in reverse date order (oldest first)
	 * @param array $exts If set, only find files with these extensions
	 * @return array A sorted array of absolute filesystem paths
	 */
	 public static function scandir_chrono(string $path, bool $reverse = false, ?array $exts = []): array {

	    /* Fail if the directory can't be opened */
	    if (!(is_dir($path) && $dir = opendir($path))) {
	        return [];
	    }

	    /* An array to hold the results */
	    $files = [];

	    while (($file = readdir($dir)) !== false) {
	        /* Skip anything that's not a regular file */
	        if (filetype($path . '/' . $file) !== 'file') {
	            continue;
	        }
	        /* If extensions were provided and this file doesn't match, skip it */
	        if (!empty($exts) && !in_array(pathinfo($path . '/' . $file,
	                                PATHINFO_EXTENSION), $exts)) {
	            continue;
	        }
	        /* Add this file to the array with its modification time as the key */
	        $files[filemtime($path . '/' . $file)] = $file;
	    }
	    closedir($dir);

	    /* Sort and return the array */
	    $fn = $reverse ? 'krsort' : 'ksort';
	    $fn($files);
	    return $files;
	 }

     /**
      * Restore a serialized object from file.
      * @param string $filename : The name of the file from which to get serialized object.
      * @param bool   $throw_error: Whether to fail loudly or quietly
      * */
	 public static function unserialize_from_file(string $filename, bool $throw_error = false) : mixed{
	 	 if(!file_exists($filename)){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("The file to unserialize does not exist!");
	 	 	 }

	 	 	 return false;
	 	 }

	 	 $contents = file_get_contents($filename);
	 	 if($contents === false){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("Could not load file contents!");
	 	 	 }

	 	 	 return false;
	 	 }

	 	 $tracker = unserialize($contents);
	 	 if($tracker === false){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("Could not unserialize file contents!");
	 	 	 }

	 	 	 return false;
	 	 }

         return $tracker;
	 }

     /**
      * Serialize an object and save the serialized object to file.
      * @param string $filename: The path and file name to save.
      * @param mixed  $object:   The object to serialize.
      * */
	 public static function serialize_to_file(string $filename, mixed $object) : bool{
	 	 $ser = serialize($object);
         return file_put_contents($filename, $ser);
	 }
}
?>