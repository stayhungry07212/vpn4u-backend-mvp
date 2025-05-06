import React, { useState, useEffect } from 'react';
import { PieChart, Pie, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell } from 'recharts';
import { Server, Users, Activity, Globe, Shield, Cpu, Database, BarChart2 } from 'lucide-react';

// Mock API response for demo purposes
// const fetchDashboardData = () => {
//   return new Promise((resolve) => {
//     setTimeout(() => {
//       resolve({
//         active_users: 1284,
//         active_connections: 876,
//         online_servers: 42,
//         total_bandwidth_today_mb: 14786,
//         subscriptions_by_plan: {
//           free_trial: 352,
//           basic: 587,
//           premium: 289,
//           business: 56
//         },
//         recent_registrations: 78,
//         server_stats: {
//           online: 42,
//           offline: 3,
//           maintenance: 5
//         },
//         device_stats: {
//           windows: 345,
//           macos: 189,
//           linux: 123,
//           android: 156,
//           ios: 63
//         },
//         region_stats: {
//           'us-east': 256,
//           'us-west': 189,
//           'eu-west': 235,
//           'eu-central': 122,
//           'ap-east': 43,
//           'ap-south': 31
//         },
//         daily_connections: {
//           '2025-05-01': 845,
//           '2025-05-02': 912,
//           '2025-05-03': 876,
//           '2025-05-04': 923,
//           '2025-05-05': 876
//         }
//       });
//     }, 500);
//   });
// };

const fetchDashboardData = async () => {
    try {
      const response = await fetch('/api/admin/dashboard');
      if (!response.ok) {
        throw new Error('Failed to fetch dashboard data');
      }
      return await response.json();
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      throw error;
    }
  };

const AdminDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadData = async () => {
      try {
        const data = await fetchDashboardData();
        setDashboardData(data);
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="text-xl font-semibold">Loading dashboard data...</div>
      </div>
    );
  }

  // Format data for charts
  const subscriptionData = Object.entries(dashboardData.subscriptions_by_plan).map(([name, value]) => ({
    name: name.charAt(0).toUpperCase() + name.slice(1).replace('_', ' '),
    value
  }));

  const deviceData = Object.entries(dashboardData.device_stats).map(([name, value]) => ({
    name: name.charAt(0).toUpperCase() + name.slice(1),
    value
  }));

  const regionData = Object.entries(dashboardData.region_stats).map(([name, value]) => ({
    name: name.toUpperCase(),
    value
  }));

  const connectionData = Object.entries(dashboardData.daily_connections).map(([date, count]) => ({
    date: date.split('-').slice(1).join('/'), // MM/DD format
    connections: count
  }));

  const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#ffc658'];

  return (
    <div className="bg-gray-100 min-h-screen">
      <div className="bg-blue-600 text-white p-4">
        <div className="container mx-auto">
          <h1 className="text-2xl font-bold">VPN4U Admin Dashboard</h1>
          <p className="text-sm opacity-80">Real-time monitoring and management</p>
        </div>
      </div>

      <div className="container mx-auto p-4">
        {/* Stats Overview */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <StatCard 
            icon={<Users size={24} />} 
            title="Active Users" 
            value={dashboardData.active_users.toLocaleString()} 
            color="blue"
          />
          <StatCard 
            icon={<Activity size={24} />} 
            title="Active Connections" 
            value={dashboardData.active_connections.toLocaleString()} 
            color="green"
          />
          <StatCard 
            icon={<Server size={24} />} 
            title="Online Servers" 
            value={dashboardData.online_servers.toLocaleString()} 
            color="purple"
          />
          <StatCard 
            icon={<Database size={24} />} 
            title="Bandwidth Today" 
            value={`${(dashboardData.total_bandwidth_today_mb / 1024).toFixed(2)} GB`} 
            color="orange"
          />
        </div>

        {/* Charts Row 1 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
          <div className="bg-white rounded-lg shadow p-4">
            <h2 className="text-lg font-semibold mb-4">Connection Trends</h2>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={connectionData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="date" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Line type="monotone" dataKey="connections" stroke="#3B82F6" strokeWidth={2} />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-4">
            <h2 className="text-lg font-semibold mb-4">Subscription Distribution</h2>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={subscriptionData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {subscriptionData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>

        {/* Charts Row 2 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
          <div className="bg-white rounded-lg shadow p-4">
            <h2 className="text-lg font-semibold mb-4">Device Distribution</h2>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={deviceData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {deviceData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-4">
            <h2 className="text-lg font-semibold mb-4">Regional Distribution</h2>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={regionData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {regionData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>

        {/* Server Management Section */}
        <div className="bg-white rounded-lg shadow p-4 mb-4">
          <h2 className="text-lg font-semibold mb-4">Server Management</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Server Name
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Region
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Load
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                <tr>
                  <td className="px-6 py-4 whitespace-nowrap">US East 1</td>
                  <td className="px-6 py-4 whitespace-nowrap">us-east</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Online
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">45%</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button className="text-blue-600 hover:text-blue-900 mr-2">Details</button>
                    <button className="text-red-600 hover:text-red-900">Restart</button>
                  </td>
                </tr>
                <tr>
                  <td className="px-6 py-4 whitespace-nowrap">EU West 1</td>
                  <td className="px-6 py-4 whitespace-nowrap">eu-west</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Online
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">28%</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button className="text-blue-600 hover:text-blue-900 mr-2">Details</button>
                    <button className="text-red-600 hover:text-red-900">Restart</button>
                  </td>
                </tr>
                <tr>
                  <td className="px-6 py-4 whitespace-nowrap">AP East 1</td>
                  <td className="px-6 py-4 whitespace-nowrap">ap-east</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                      Maintenance
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">0%</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button className="text-blue-600 hover:text-blue-900 mr-2">Details</button>
                    <button className="text-green-600 hover:text-green-900">Activate</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div className="mt-4">
            <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2">
              <Server size={16} /> Add New Server
            </button>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-wrap gap-4 mt-4">
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2">
            <Users size={16} /> User Management
          </button>
          <button className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-2">
            <Globe size={16} /> Connection Status
          </button>
          <button className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded flex items-center gap-2">
            <Shield size={16} /> Security Logs
          </button>
          <button className="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded flex items-center gap-2">
            <BarChart2 size={16} /> Generate Reports
          </button>
        </div>
      </div>
    </div>
  );
};

// Stat Card Component
const StatCard = ({ icon, title, value, color }) => {
  const colorClasses = {
    blue: 'bg-blue-100 text-blue-800',
    green: 'bg-green-100 text-green-800',
    purple: 'bg-purple-100 text-purple-800',
    orange: 'bg-orange-100 text-orange-800',
  };

  return (
    <div className="bg-white rounded-lg shadow p-4">
      <div className="flex items-center mb-3">
        <div className={`p-2 rounded-full ${colorClasses[color]} mr-3`}>
          {icon}
        </div>
        <h3 className="text-gray-600 text-sm">{title}</h3>
      </div>
      <div className="text-2xl font-bold">{value}</div>
    </div>
  );
};

export default AdminDashboard;
