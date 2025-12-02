import { useEffect, useState } from "react";
import {
  Bar,
  Line
} from "react-chartjs-2";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  TimeScale,
} from "chart.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  TimeScale
);

function App() {
  const [usagePerToilet, setUsagePerToilet] = useState([]);
  const [hourlyUsage, setHourlyUsage] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      try {
        const [perToiletRes, hourlyRes] = await Promise.all([
          fetch("http://localhost:8000/api/usage/per-toilet"),
          fetch("http://localhost:8000/api/usage/hourly"),
        ]);

        const perToiletJson = await perToiletRes.json();
        const hourlyJson = await hourlyRes.json();

        setUsagePerToilet(perToiletJson);
        setHourlyUsage(hourlyJson);
      } catch (err) {
        console.error("Error fetching:", err);
      } finally {
        setLoading(false);
      }
    }

    fetchData();
    const interval = setInterval(fetchData, 10000);
    return () => clearInterval(interval);
  }, []);

  if (loading) {
    return <div style={{ padding: 20 }}>Loading...</div>;
  }

  // 📊 Bar chart: إجمالي استخدام بالدقايق لكل حمام
  const barData = {
    labels: usagePerToilet.map((u) => `Toilet ${u.toilet_id}`),
    datasets: [
      {
        label: "Total occupied minutes (approx)",
        data: usagePerToilet.map((u) => u.total_occupied_minutes),
      },
    ],
  };

  // 📈 Line chart: استخدام بالساعة (ناخد مثلاً حمام واحد أو كلهم stacked)
  const uniqueHours = [
    ...new Set(hourlyUsage.map((h) => h.hour)),
  ].sort();

  const toilets = [
    ...new Set(hourlyUsage.map((h) => h.toilet_id)),
  ].sort();

  const lineDatasets = toilets.map((toiletId) => {
    const dataForToilet = uniqueHours.map((hour) => {
      const row = hourlyUsage.find(
        (h) => h.toilet_id === toiletId && h.hour === hour
      );
      return row ? row.occupied_minutes : 0;
    });

    return {
      label: `Toilet ${toiletId}`,
      data: dataForToilet,
      tension: 0.3,
    };
  });

  const lineData = {
    labels: uniqueHours.map((h) =>
      new Date(h).toLocaleString()
    ),
    datasets: lineDatasets,
  };

  return (
    <div style={{ padding: 20, fontFamily: "sans-serif" }}>
      <h1>Toilets Usage Dashboard</h1>

      <section style={{ marginBottom: 40 }}>
        <h2>Usage per toilet (minutes)</h2>
        <Bar data={barData} />
      </section>

      <section style={{ marginBottom: 40 }}>
        <h2>Hourly usage (minutes / hour)</h2>
        <Line data={lineData} />
      </section>

      <section>
        <h2>Raw usage summary</h2>
        <table border="1" cellPadding="5">
          <thead>
            <tr>
              <th>Toilet</th>
              <th>Occupied hits</th>
              <th>Total occupied (min)</th>
              <th>Avg visit (min, approx)</th>
            </tr>
          </thead>
          <tbody>
            {usagePerToilet.map((u) => (
              <tr key={u.toilet_id}>
                <td>{u.toilet_id}</td>
                <td>{u.occupied_hits}</td>
                <td>{u.total_occupied_minutes}</td>
                <td>{u.avg_visit_minutes_approx}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </div>
  );
}

export default App;
