# Create admin dashboard directory
mkdir -p public/admin

# Create index.html file
cat > public/admin/index.html << 'EOL'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN4U Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/react@17.0.2/umd/react.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.2/umd/react-dom.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/recharts@2.1.9/umd/Recharts.min.js"></script>
</head>
<body>
    <div id="root"></div>
    <script src="js/dashboard.js"></script>
</body>
</html>
EOL

# Create js directory
mkdir -p public/admin/js

# You'll need to transpile the React component to JS
# For demo purposes, you can use create-react-app separately to build 
# the admin dashboard and copy the build files to public/admin