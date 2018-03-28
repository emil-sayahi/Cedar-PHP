<?php

function userSidebarSetting($user, $page)
{
    global $dbc;

    $post_count = $dbc->prepare('SELECT COUNT(id) FROM posts WHERE post_by_id = ?');
    $post_count->bind_param('i', $user['user_id']);
    $post_count->execute();
    $result_count = $post_count->get_result();
    $post_amount = $result_count->fetch_assoc();

    $reply_count = $dbc->prepare('SELECT COUNT(reply_by_id) FROM replies WHERE reply_by_id = ?');
    $reply_count->bind_param('i', $user['user_id']);
    $reply_count->execute();
    $result_count = $reply_count->get_result();
    $reply_amount = $result_count->fetch_assoc();

    $yeah_count = $dbc->prepare('SELECT COUNT(yeah_by) FROM yeahs WHERE yeah_by = ?');
    $yeah_count->bind_param('i', $user['user_id']);
    $yeah_count->execute();
    $result_count = $yeah_count->get_result();
    $yeah_amount = $result_count->fetch_assoc();

    $nah_count = $dbc->prepare('SELECT COUNT(nah_by) FROM nahs WHERE nah_by = ?');
    $nah_count->bind_param('i', $user['user_id']);
    $nah_count->execute();
    $result_count = $nah_count->get_result();
    $nah_amount = $result_count->fetch_assoc();

    echo '<div class="sidebar-setting sidebar-container">
    <div class="sidebar-post-menu">
      <a href="/users/'. $user['user_name'] .'/posts" class="sidebar-menu-post with-count symbol'. ($page == 1 ? ' selected' : '') .'">
        <span>All Posts</span>
        <span class="post-count">
          <span class="test-post-count">'. $post_amount['COUNT(id)'] .'</span>
        </span>
      </a>
      <a href="/users/'. $user['user_name'] .'/replies" class="sidebar-menu-replies with-count symbol'. ($page == 2 ? ' selected' : '') .'">
        <span>Replies</span>
        <span class="post-count">
          <span class="test-reply-count">'. $reply_amount['COUNT(reply_by_id)'] .'</span>
        </span>
      </a>
      <a href="/users/'. $user['user_name'] .'/yeahs" class="sidebar-menu-empathies with-count symbol'. ($page == 3 ? ' selected' : '') .'">
        <span>Yeahs</span>
        <span class="post-count">
          <span class="test-empathy-count">'. $yeah_amount['COUNT(yeah_by)'] .'</span>
        </span>
      </a>';

    if ($user['user_id'] == $_SESSION['user_id']) {
        echo '<a href="/users/'. $user['user_name'] .'/nahs" class="sidebar-menu-nahs with-count symbol'. ($page == 4 ? ' selected' : '') .'">
    	<span>Nahs</span>
    	<span class="post-count">
    	<span class="test-empathy-count">'. $nah_amount['COUNT(nah_by)'] .'</span>
        </span>
        </a>';
    }
      
    echo '</div></div>';
}

function userContent($user, $selected)
{
    global $dbc;

    $following_count = $dbc->prepare('SELECT COUNT(follow_by) FROM follows WHERE follow_by = ?');
    $following_count->bind_param('i', $user['user_id']);
    $following_count->execute();
    $result_count = $following_count->get_result();
    $following_amount = $result_count->fetch_assoc();

    $followers_count = $dbc->prepare('SELECT COUNT(follow_to) FROM follows WHERE follow_to = ?');
    $followers_count->bind_param('i', $user['user_id']);
    $followers_count->execute();
    $result_count = $followers_count->get_result();
    $followers_amount = $result_count->fetch_assoc();

    $get_fav_post = $dbc->prepare('SELECT * FROM profiles INNER JOIN posts ON id = fav_post AND deleted = 0 WHERE user_id = ?');
    $get_fav_post->bind_param('i', $user['user_id']);
    $get_fav_post->execute();
    $result_fav_post = $get_fav_post->get_result();
    $fav_post = $result_fav_post->fetch_assoc();

    echo '<div class="sidebar-container">
    '. (isset($fav_post['post_image']) ? '<a href="/posts/'.$fav_post['id'].'" id="sidebar-cover" style="background-image:url('.$fav_post['post_image'].')">
        <img src="'.$fav_post['post_image'].'" class="sidebar-cover-image">
      </a>':'').'
      <div id="sidebar-profile-body" class="'.(isset($fav_post['post_image'])?'with-profile-post-image':'').'">
        <div class="icon-container'.($user['user_level'] > 1 ? ' verified' : '').'">
          <a href="/users/'.$user['user_name'] .'/posts">
            <img src="'.printFace($user['user_face'], 0).'" alt="'. htmlspecialchars($user['nickname'], ENT_QUOTES) .'" id="icon">
          </a>
        </div>
        '.(isset($user['organization'])?'<span class="user-organization">'.$user['organization'].'</span>':'').'
        <a href="/users/'. $user['user_name'] .'/posts" '.(isset($user['name_color']) ? 'style="color: '. $user['name_color'] .'"' : '').' class="nick-name">'. htmlspecialchars($user['nickname'], ENT_QUOTES) .'</a>
        <p class="id-name">'. $user['user_name'] .'</p>
      </div>';

    if (!empty($_SESSION['signed_in']) && ($_SESSION['user_id'] !== $user['user_id'])) {
        echo '<div class="user-action-content"><div class="toggle-button" style="text-align: center;">
    	<button type="button" data-user-id="'. $user['user_id'] .'" class="';

        $check_followed = $dbc->prepare('SELECT * FROM follows WHERE follow_by = ? AND follow_to = ? LIMIT 1');
        $check_followed->bind_param('ii', $_SESSION['user_id'], $user['user_id']);
        $check_followed->execute();
        $followed_result = $check_followed->get_result();

        if (!$followed_result->num_rows == 0) {
            echo 'unfollow';
        } else {
            echo 'follow';
        }
        echo '-button button symbol">Follow</button>
		</div></div>';
    } elseif (!empty($_SESSION['signed_in']) && ($_SESSION['user_id'] == $user['user_id']) && !empty($selected)) {
        echo '<div id="edit-profile-settings"><a class="button symbol" href="/settings/profile">Profile Settings</a></div>';
    }

    echo '<ul id="sidebar-profile-status">
        <li><a href="/users/'. $user['user_name'] .'/following"'. ($selected == "following" ? 'class="selected"' : '') .'><span class="number">'. $following_amount['COUNT(follow_by)'] .'</span>Following</a></li>
        <li><a href="/users/'. $user['user_name'] .'/followers"'. ($selected == "followers" ? 'class="selected"' : '') .'><span class="number">'. $followers_amount['COUNT(follow_to)'] .'</span>Followers</a></li>
      </ul>
    </div>';
}

function sidebarSetting()
{
    global $dbc;

    $get_announce = $dbc->prepare('SELECT * FROM titles WHERE type = 5 LIMIT 1');
    $get_announce->execute();
    $announce_result = $get_announce->get_result();
    $announce = $announce_result->fetch_assoc();
    echo '<div class="sidebar-setting sidebar-container">
		  <ul>

			<li><a href="/settings/account" class="sidebar-menu-setting symbol"><span>Cedar Settings</span></a></li>
			<li><a href="/titles/'.$announce['title_id'].'" class="sidebar-menu-info symbol"><span>Cedar Announcements</span></a></li>
	        
		  </ul>
		</div>';
}


function noUser()
{
    echo '<title>Cedar - Error</title><div class="no-content track-error" data-track-error="404"><div><p>The user could not be found.</p></div></div>';
}

function userInfo($user)
{

    global $dbc;

    $get_prof = $dbc->prepare('SELECT * FROM profiles WHERE user_id = ?');
    $get_prof->bind_param('i', $user['user_id']);
    $get_prof->execute();
    $prof_result = $get_prof->get_result();
    $profile = $prof_result->fetch_assoc();

    $get_user_level = $dbc->prepare('SELECT user_level FROM users WHERE user_id = ?');
    $get_user_level->bind_param('i', $_SESSION['user_id']);
    $get_user_level->execute();
    $user_level_result = $get_user_level->get_result();
    $user_level = $user_level_result->fetch_assoc();

    $get_yeahs = $dbc->prepare('SELECT COUNT(yeah_id) FROM yeahs WHERE yeah_post IN (SELECT id FROM posts WHERE post_by_id = ?) OR yeah_post IN (SELECT reply_id FROM replies WHERE reply_by_id = ?)');
    $get_yeahs->bind_param('ii', $user['user_id'], $user['user_id']);
    $get_yeahs->execute();
    $yeahs_result = $get_yeahs->get_result();
    $yeahs = $yeahs_result->fetch_assoc();

    echo '<div class="sidebar-container sidebar-profile">';
    if (!is_null($profile['bio'])) {
        echo '<div class="profile-comment"><p class="js-truncated-text">';
        if (mb_strlen($profile['bio']) <= 99) {
            echo nl2br($profile['bio']) .'</p></div>';
        } else {
            echo nl2br(mb_substr($profile['bio'], 0, 97)) .'...</p>
			<p class="js-full-text none">'.nl2br($profile['bio']).'</p>
			<button type="button" class="description-more-button js-open-truncated-text-button">Show More</button></div>';
        }
    }

    echo '<div class="user-data">
      <div class="user-main-profile data-content">
        <h4><span>Country</span></h4>
        <div class="note">';

    switch ($profile['country']) {
        case 1:
            echo "Afghanistan";
            break;
        case 2:
            echo "Albania";
            break;
        case 3:
            echo "Algeria";
            break;
        case 4:
            echo "American Samoa";
            break;
        case 5:
            echo "Andorra";
            break;
        case 6:
            echo "Angola";
            break;
        case 7:
            echo "Anguilla";
            break;
        case 8:
            echo "Antarctica";
            break;
        case 9:
            echo "Antigua and Barbuda";
            break;
        case 10:
            echo "Argentina";
            break;
        case 11:
            echo "Armenia";
            break;
        case 12:
            echo "Aruba";
            break;
        case 13:
            echo "Australia";
            break;
        case 14:
            echo "Austria";
            break;
        case 15:
            echo "Azerbaijan";
            break;
        case 16:
            echo "Bahamas";
            break;
        case 17:
            echo "Bahrain";
            break;
        case 18:
            echo "Bangladesh";
            break;
        case 19:
            echo "Barbados";
            break;
        case 20:
            echo "Belarus";
            break;
        case 21:
            echo "Belgium";
            break;
        case 22:
            echo "Belize";
            break;
        case 23:
            echo "Benin";
            break;
        case 24:
            echo "Bermuda";
            break;
        case 25:
            echo "Bhutan";
            break;
        case 26:
            echo "Bolivia";
            break;
        case 27:
            echo "Bosnia and Herzegovina";
            break;
        case 28:
            echo "Botswana";
            break;
        case 29:
            echo "Bouvet Island";
            break;
        case 30:
            echo "Brazil";
            break;
        case 31:
            echo "British Antarctic Territory";
            break;
        case 32:
            echo "British Indian Ocean Territory";
            break;
        case 33:
            echo "British Virgin Islands";
            break;
        case 34:
            echo "Brunei";
            break;
        case 35:
            echo "Bulgaria";
            break;
        case 36:
            echo "Burkina Faso";
            break;
        case 37:
            echo "Burundi";
            break;
        case 38:
            echo "Cambodia";
            break;
        case 39:
            echo "Cameroon";
            break;
        case 40:
            echo "Canada";
            break;
        case 41:
            echo "Canton and Enderbury Islands";
            break;
        case 42:
            echo "Cape Verde";
            break;
        case 43:
            echo "Cayman Islands";
            break;
        case 44:
            echo "Central African Republic";
            break;
        case 45:
            echo "Chad";
            break;
        case 46:
            echo "Chile";
            break;
        case 47:
            echo "China";
            break;
        case 48:
            echo "Christmas Island";
            break;
        case 49:
            echo "Cocos [Keeling] Islands";
            break;
        case 50:
            echo "Colombia";
            break;
        case 51:
            echo "Comoros";
            break;
        case 52:
            echo "Congo - Brazzaville";
            break;
        case 53:
            echo "Congo - Kinshasa";
            break;
        case 54:
            echo "Cook Islands";
            break;
        case 55:
            echo "Costa Rica";
            break;
        case 56:
            echo "Croatia";
            break;
        case 57:
            echo "Cuba";
            break;
        case 58:
            echo "Cyprus";
            break;
        case 59:
            echo "Czech Republic";
            break;
        case 60:
            echo "Côte d’Ivoire";
            break;
        case 61:
            echo "Denmark";
            break;
        case 62:
            echo "Djibouti";
            break;
        case 63:
            echo "Dominica";
            break;
        case 64:
            echo "Dominican Republic";
            break;
        case 65:
            echo "Dronning Maud Land";
            break;
        case 66:
            echo "East Germany";
            break;
        case 67:
            echo "Ecuador";
            break;
        case 68:
            echo "Egypt";
            break;
        case 69:
            echo "El Salvador";
            break;
        case 70:
            echo "Equatorial Guinea";
            break;
        case 71:
            echo "Eritrea";
            break;
        case 72:
            echo "Estonia";
            break;
        case 73:
            echo "Ethiopia";
            break;
        case 74:
            echo "Falkland Islands";
            break;
        case 75:
            echo "Faroe Islands";
            break;
        case 76:
            echo "Fiji";
            break;
        case 77:
            echo "Finland";
            break;
        case 78:
            echo "France";
            break;
        case 79:
            echo "French Guiana";
            break;
        case 80:
            echo "French Polynesia";
            break;
        case 81:
            echo "French Southern Territories";
            break;
        case 82:
            echo "French Southern and Antarctic Territories";
            break;
        case 83:
            echo "Gabon";
            break;
        case 84:
            echo "Gambia";
            break;
        case 85:
            echo "Georgia";
            break;
        case 86:
            echo "Germany";
            break;
        case 87:
            echo "Ghana";
            break;
        case 88:
            echo "Gibraltar";
            break;
        case 89:
            echo "Greece";
            break;
        case 90:
            echo "Greenland";
            break;
        case 91:
            echo "Grenada";
            break;
        case 92:
            echo "Guadeloupe";
            break;
        case 93:
            echo "Guam";
            break;
        case 94:
            echo "Guatemala";
            break;
        case 95:
            echo "Guernsey";
            break;
        case 96:
            echo "Guinea";
            break;
        case 97:
            echo "Guinea-Bissau";
            break;
        case 98:
            echo "Guyana";
            break;
        case 99:
            echo "Haiti";
            break;
        case 100:
            echo "Heard Island and McDonald Islands";
            break;
        case 101:
            echo "Honduras";
            break;
        case 102:
            echo "Hong Kong SAR China";
            break;
        case 103:
            echo "Hungary";
            break;
        case 104:
            echo "Iceland";
            break;
        case 105:
            echo "India";
            break;
        case 106:
            echo "Indonesia";
            break;
        case 107:
            echo "Iran";
            break;
        case 108:
            echo "Iraq";
            break;
        case 109:
            echo "Ireland";
            break;
        case 110:
            echo "Isle of Man";
            break;
        case 111:
            echo "Israel";
            break;
        case 112:
            echo "Italy";
            break;
        case 113:
            echo "Jamaica";
            break;
        case 114:
            echo "Japan";
            break;
        case 115:
            echo "Jersey";
            break;
        case 116:
            echo "Johnston Island";
            break;
        case 117:
            echo "Jordan";
            break;
        case 118:
            echo "Kazakhstan";
            break;
        case 119:
            echo "Kenya";
            break;
        case 120:
            echo "Kiribati";
            break;
        case 121:
            echo "Kuwait";
            break;
        case 122:
            echo "Kyrgyzstan";
            break;
        case 123:
            echo "Laos";
            break;
        case 124:
            echo "Latvia";
            break;
        case 125:
            echo "Lebanon";
            break;
        case 126:
            echo "Lesotho";
            break;
        case 127:
            echo "Liberia";
            break;
        case 128:
            echo "Libya";
            break;
        case 129:
            echo "Liechtenstein";
            break;
        case 130:
            echo "Lithuania";
            break;
        case 131:
            echo "Luxembourg";
            break;
        case 132:
            echo "Macau SAR China";
            break;
        case 133:
            echo "Macedonia";
            break;
        case 134:
            echo "Madagascar";
            break;
        case 135:
            echo "Malawi";
            break;
        case 136:
            echo "Malaysia";
            break;
        case 137:
            echo "Maldives";
            break;
        case 138:
            echo "Mali";
            break;
        case 139:
            echo "Malta";
            break;
        case 140:
            echo "Marshall Islands";
            break;
        case 141:
            echo "Martinique";
            break;
        case 142:
            echo "Mauritania";
            break;
        case 143:
            echo "Mauritius";
            break;
        case 144:
            echo "Mayotte";
            break;
        case 145:
            echo "Metropolitan France";
            break;
        case 146:
            echo "Mexico";
            break;
        case 147:
            echo "Micronesia";
            break;
        case 148:
            echo "Midway Islands";
            break;
        case 149:
            echo "Moldova";
            break;
        case 150:
            echo "Monaco";
            break;
        case 151:
            echo "Mongolia";
            break;
        case 152:
            echo "Montenegro";
            break;
        case 153:
            echo "Montserrat";
            break;
        case 154:
            echo "Morocco";
            break;
        case 155:
            echo "Mozambique";
            break;
        case 156:
            echo "Myanmar [Burma]";
            break;
        case 157:
            echo "Namibia";
            break;
        case 158:
            echo "Nauru";
            break;
        case 159:
            echo "Nepal";
            break;
        case 160:
            echo "Netherlands";
            break;
        case 161:
            echo "Netherlands Antilles";
            break;
        case 162:
            echo "Neutral Zone";
            break;
        case 163:
            echo "New Caledonia";
            break;
        case 164:
            echo "New Zealand";
            break;
        case 165:
            echo "Nicaragua";
            break;
        case 166:
            echo "Niger";
            break;
        case 167:
            echo "Nigeria";
            break;
        case 168:
            echo "Niue";
            break;
        case 169:
            echo "Norfolk Island";
            break;
        case 170:
            echo "North Korea";
            break;
        case 171:
            echo "North Vietnam";
            break;
        case 172:
            echo "Northern Mariana Islands";
            break;
        case 173:
            echo "Norway";
            break;
        case 174:
            echo "Oman";
            break;
        case 175:
            echo "Pacific Islands Trust Territory";
            break;
        case 176:
            echo "Pakistan";
            break;
        case 177:
            echo "Palau";
            break;
        case 178:
            echo "Palestinian Territories";
            break;
        case 179:
            echo "Panama";
            break;
        case 180:
            echo "Panama Canal Zone";
            break;
        case 181:
            echo "Papua New Guinea";
            break;
        case 182:
            echo "Paraguay";
            break;
        case 183:
            echo "People's Democratic Republic of Yemen";
            break;
        case 184:
            echo "Peru";
            break;
        case 185:
            echo "Philippines";
            break;
        case 186:
            echo "Pitcairn Islands";
            break;
        case 187:
            echo "Poland";
            break;
        case 188:
            echo "Portugal";
            break;
        case 189:
            echo "Puerto Rico";
            break;
        case 190:
            echo "Qatar";
            break;
        case 191:
            echo "Romania";
            break;
        case 192:
            echo "Russia";
            break;
        case 193:
            echo "Rwanda";
            break;
        case 194:
            echo "Réunion";
            break;
        case 195:
            echo "Saint Barthélemy";
            break;
        case 196:
            echo "Saint Helena";
            break;
        case 197:
            echo "Saint Kitts and Nevis";
            break;
        case 198:
            echo "Saint Lucia";
            break;
        case 199:
            echo "Saint Martin";
            break;
        case 200:
            echo "Saint Pierre and Miquelon";
            break;
        case 201:
            echo "Saint Vincent and the Grenadines";
            break;
        case 202:
            echo "Samoa";
            break;
        case 203:
            echo "San Marino";
            break;
        case 204:
            echo "Saudi Arabia";
            break;
        case 205:
            echo "Senegal";
            break;
        case 206:
            echo "Serbia";
            break;
        case 207:
            echo "Serbia and Montenegro";
            break;
        case 208:
            echo "Seychelles";
            break;
        case 209:
            echo "Sierra Leone";
            break;
        case 210:
            echo "Singapore";
            break;
        case 211:
            echo "Slovakia";
            break;
        case 212:
            echo "Slovenia";
            break;
        case 213:
            echo "Solomon Islands";
            break;
        case 214:
            echo "Somalia";
            break;
        case 215:
            echo "South Africa";
            break;
        case 216:
            echo "South Georgia and the South Sandwich Islands";
            break;
        case 217:
            echo "South Korea";
            break;
        case 218:
            echo "Spain";
            break;
        case 219:
            echo "Sri Lanka";
            break;
        case 220:
            echo "Sudan";
            break;
        case 221:
            echo "Suriname";
            break;
        case 222:
            echo "Svalbard and Jan Mayen";
            break;
        case 223:
            echo "Swaziland";
            break;
        case 224:
            echo "Sweden";
            break;
        case 225:
            echo "Switzerland";
            break;
        case 226:
            echo "Syria";
            break;
        case 227:
            echo "São Tomé and Príncipe";
            break;
        case 228:
            echo "Taiwan";
            break;
        case 229:
            echo "Tajikistan";
            break;
        case 230:
            echo "Tanzania";
            break;
        case 231:
            echo "Thailand";
            break;
        case 232:
            echo "Timor-Leste";
            break;
        case 233:
            echo "Togo";
            break;
        case 234:
            echo "Tokelau";
            break;
        case 235:
            echo "Tonga";
            break;
        case 236:
            echo "Trinidad and Tobago";
            break;
        case 237:
            echo "Tunisia";
            break;
        case 238:
            echo "Turkey";
            break;
        case 239:
            echo "Turkmenistan";
            break;
        case 240:
            echo "Turks and Caicos Islands";
            break;
        case 241:
            echo "Tuvalu";
            break;
        case 242:
            echo "U.S. Minor Outlying Islands";
            break;
        case 243:
            echo "U.S. Miscellaneous Pacific Islands";
            break;
        case 244:
            echo "U.S. Virgin Islands";
            break;
        case 245:
            echo "Uganda";
            break;
        case 246:
            echo "Ukraine";
            break;
        case 247:
            echo "Union of Soviet Socialist Republics";
            break;
        case 248:
            echo "United Arab Emirates";
            break;
        case 249:
            echo "United Kingdom";
            break;
        case 250:
            echo "United States";
            break;
        case 251:
            echo "Unknown or Invalid Region";
            break;
        case 252:
            echo "Uruguay";
            break;
        case 253:
            echo "Uzbekistan";
            break;
        case 254:
            echo "Vanuatu";
            break;
        case 255:
            echo "Vatican City";
            break;
        case 256:
            echo "Venezuela";
            break;
        case 257:
            echo "Vietnam";
            break;
        case 258:
            echo "Wake Island";
            break;
        case 259:
            echo "Wallis and Futuna";
            break;
        case 260:
            echo "Western Sahara";
            break;
        case 261:
            echo "Yemen";
            break;
        case 262:
            echo "Zambia";
            break;
        case 263:
            echo "Zimbabwe";
            break;
        case 264:
            echo "Åland Islands";
            break;

        default:
            echo "Not set.";
    }

    echo '</div>
    <h4><span>Birthday</span></h4>
    <div class="note birthday">'. (isset($profile['birthday']) ? date('m/d', strtotime($profile['birthday'])) : 'Not set.') .'</div>
    </div>
    <div class="game-skill data-content">
	  <h4><span>Status</span></h4>
	  <div class="note"><span class="test-game-skill symbol'.(strtotime($profile['last_online'])>time()-35?'">On':' offline">Off').'line</span></div>
    </div>

    <div class="yeahs-received'. ($user_level['user_level'] > 0 ? ' data-content' : '') .'"><h4><span>Yeahs Received</span></h4><div class="note">'. number_format($yeahs['COUNT(yeah_id)']) .'</div></div>';


    if ($user_level['user_level'] > 0) {
        echo '<div class="user-id data-content"><h4><span>User ID</span></h4><div class="note">'. $user['user_id'] .'</div></div>
    	<div class="ip"><h4><span>IP Address</span></h4><div class="note">'. $user['ip'] .'</div></div>';
    }


    echo '</div></div>

    <div class="sidebar-container sidebar-favorite-community">
      <h4><a href="/'.(!empty($_SESSION['signed_in']) && ($_SESSION['user_id'] == $user['user_id']) ? 'communities' : 'users/'.$user['user_name']).'/favorites'.'" class="symbol favorite-community-button"><span>Favorite Communities</span></a></h4>


      <ul class="test-favorite-communities">';


    $get_fav_titles = $dbc->prepare('SELECT titles.* FROM titles, favorite_titles WHERE titles.title_id = favorite_titles.title_id AND favorite_titles.user_id = ? ORDER BY favorite_titles.fav_id DESC LIMIT 10');
    $get_fav_titles->bind_param('i', $user['user_id']);
    $get_fav_titles->execute();
    $fav_titles_result = $get_fav_titles->get_result();
    $empty_space = 0;
    while ($fav_titles = $fav_titles_result->fetch_assoc()) {
        echo '<li class="favorite-community"><a href="/titles/'.$fav_titles['title_id'].'"><span class="icon-container"><img id="icon" src="'.$fav_titles['title_icon'].'"></span></a>              
          <span class="platform-tag">';
        switch ($fav_titles['type']) {
            case 1:
                echo '<img src="/assets/img/platform-tag-wiiu.png">';
                break;
            case 2:
                echo '<img src="/assets/img/platform-tag-3ds.png">';
                break;
            case 3:
                echo '<img src="/assets/img/platform-tag-wiiu-3ds.png">';
                break;
            case 4:
                echo '<img src="/assets/img/platform-tag-switch.png">';
                break;
        }
        echo '</span></li>';
        $empty_space++;
    }
    for ($i = 10; $i > $empty_space; $i--) {
        echo '<li class="favorite-community empty"><span class="icon-container empty-icon"><img src="/assets/img/'. (isset($_COOKIE['dark-mode']) ? 'dark-' : '') .'empty.png" id="icon"></span></li>';
    }

    echo '</ul>


    </div>';
}
