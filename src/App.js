import React, { useState, useEffect } from "react";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";

import "./main.css";

export const periodFilter = {
  lastWeek: "visits_last_week",
  lastHalfMonth: "visits_last_half_month",
  lastMonth: "visits_last_month",
};

export const App = () => {
  const [posts, setPosts] = useState();
  const [selectedPeriod, setSelectedPeriod] = useState(periodFilter.lastWeek);

  const url = `http://localhost:8888/wp-json/mcgw/v1/statistics?period=${selectedPeriod}`;

  useEffect(() => {
    fetch(url)
      .then((res) => res.json())
      .then((result) => setPosts(result))
      .catch((error) => console.log(error));
  }, [selectedPeriod]);

  return (
    <div style={{ width: "100%", height: "auto" }}>
      <div className="header">
        <h1>Visits Graph</h1>

        <select
          name="period"
          id="period"
          onChange={(e) => {
            console.log(e.target.value);
            setSelectedPeriod(e.target.value);
          }}
        >
          <option value={periodFilter.lastWeek}>Last 7 days</option>
          <option value={periodFilter.lastHalfMonth}>Last 15 days</option>
          <option value={periodFilter.lastMonth}>Last month</option>
        </select>
      </div>

      <ResponsiveContainer minHeight={300}>
        <BarChart data={posts}>
          <CartesianGrid strokeDasharray="2 2" />
          <XAxis dataKey="page_title" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Bar dataKey="total_visits" fill="#8884d8" />
          <Bar dataKey={selectedPeriod} fill="#82ca9d" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
};
