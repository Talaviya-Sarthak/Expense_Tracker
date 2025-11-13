document.addEventListener('DOMContentLoaded', () => {
  const menuIcon = document.getElementById('menuToggle') || document.querySelector('.menu-icon');
  const closeNavbarButton = document.querySelector('.close-navbar');
  const navbar = document.querySelector('.navbar');

  if (menuIcon && navbar && closeNavbarButton) {
    menuIcon.addEventListener('click', () => {
      navbar.classList.add('active');
    });

    closeNavbarButton.addEventListener('click', () => {
      navbar.classList.remove('active');
    });
  }

  const pathName = window.location.pathname;
  const pathLower = pathName.toLowerCase();
  const frontendIndex = pathLower.indexOf('/frontend/');
  const backendBase = frontendIndex !== -1
    ? `${window.location.origin}${pathName.slice(0, frontendIndex)}/BACKEND/`
    : `${window.location.origin}/BACKEND/`;

  const endpoints = {
    transactions: `${backendBase}get_transactions.php`,
    dashboard: `${backendBase}handle_dashboard.php`,
    download: `${backendBase}handle_download.php`,
    profile: `${backendBase}handle_profile.php`,
  };

  async function fetchJson(url, options = {}) {
    const { headers: customHeaders, ...rest } = options;
    const response = await fetch(url, {
      credentials: 'include',
      headers: { Accept: 'application/json', ...(customHeaders || {}) },
      ...rest,
    });
    const text = await response.text();

    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      console.error('Expected JSON but received:', text);
      throw new Error('Server returned an unexpected response.');
    }

    if (!response.ok) {
      const message = data.error || `Request failed with status ${response.status}`;
      const error = new Error(message);
      error.status = response.status;
      error.body = data;
      throw error;
    }

    return data;
  }

  function formatCurrency(amount) {
    return `₹${Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
    }[char]));
  }

  function toDateInputValue(date) {
    return date.toISOString().slice(0, 10);
  }

  function getDateRange(duration) {
    const now = new Date();
    now.setHours(0, 0, 0, 0);

    switch (duration) {
      case 'day': {
        const start = toDateInputValue(now);
        return { start_date: start, end_date: start };
      }
      case 'week': {
        const day = now.getDay();
        const diffToMonday = (day === 0 ? -6 : 1) - day;
        const monday = new Date(now);
        monday.setDate(now.getDate() + diffToMonday);
        const sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        return { start_date: toDateInputValue(monday), end_date: toDateInputValue(sunday) };
      }
      case 'month': {
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        return { start_date: toDateInputValue(start), end_date: toDateInputValue(end) };
      }
      case 'year': {
        const start = new Date(now.getFullYear(), 0, 1);
        const end = new Date(now.getFullYear(), 11, 31);
        return { start_date: toDateInputValue(start), end_date: toDateInputValue(end) };
      }
      default:
        return {};
    }
  }

  async function fetchTransactions({ duration = '', limit = 200 } = {}) {
    const params = new URLSearchParams({ limit });
    const range = getDateRange(duration);
    if (range.start_date) params.set('start_date', range.start_date);
    if (range.end_date) params.set('end_date', range.end_date);
    const url = `${endpoints.transactions}?${params.toString()}`;
    const data = await fetchJson(url);
    return Array.isArray(data.data) ? data.data : [];
  }

  let profilePromise = null;
  function getProfile() {
    if (!profilePromise) {
      profilePromise = (async () => {
        try {
          const data = await fetchJson(endpoints.profile);
          return data.user || null;
        } catch (error) {
          if (error.status === 401) {
            window.location.href = '2login.html';
            return null;
          }
          console.error('Failed to load profile:', error);
          return null;
        }
      })();
    }
    return profilePromise;
  }

  const profileNameTargets = document.querySelectorAll('[data-profile-name]');
  const profileEmailTargets = document.querySelectorAll('[data-profile-email]');
  const profileCountryTargets = document.querySelectorAll('[data-profile-country]');
  const profileGenderTargets = document.querySelectorAll('[data-profile-gender]');
  const profileIncomeTargets = document.querySelectorAll('[data-profile-income]');
  const profileAvatarTargets = document.querySelectorAll('[data-profile-avatar]');

  function applyProfileData(user) {
    const safeName = user?.username || '—';
    const safeEmail = user?.email || '—';
    const safeCountry = user?.country || '—';
    const safeGender = user?.gender || '—';
    const safeIncome = user?.monthly_income != null ? formatCurrency(user.monthly_income) : '—';

    profileNameTargets.forEach((el) => { el.textContent = safeName; });
    profileEmailTargets.forEach((el) => { el.textContent = safeEmail; });
    profileCountryTargets.forEach((el) => { el.textContent = safeCountry; });
    profileGenderTargets.forEach((el) => { el.textContent = safeGender; });
    profileIncomeTargets.forEach((el) => { el.textContent = safeIncome; });
    profileAvatarTargets.forEach((img) => {
      if (user?.profile_pic_path) {
        img.src = user.profile_pic_path;
      }
    });
  }

  const signupForm = document.getElementById('signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const username = signupForm.querySelector('#username').value;
      const email = signupForm.querySelector('input[type="email"]').value;
      const phone = signupForm.querySelector('input[type="tel"]').value;
      const country = signupForm.querySelector('#country').value;
      const gender = signupForm.querySelector('#gender').value;
      const income = signupForm.querySelector('#income').value;
      const profilePic = signupForm.querySelector('#profile-pic').files[0];

      if (!username || !email || !phone || !country || !gender || !income) {
        alert('Please fill in all required fields.');
        return;
      }

      if (!validateEmail(email)) {
        alert('Please enter a valid email address.');
        return;
      }

      if (!validatePhoneNumber(phone)) {
        alert('Please enter a valid phone number (10 digits).');
        return;
      }

      const userData = {
        username: username,
        email: email,
        phone: phone,
        country: country,
        gender: gender,
        monthlyIncome: parseFloat(income),
        profilePic: profilePic ? URL.createObjectURL(profilePic) : null
      };
      localStorage.setItem('currentUser ', JSON.stringify(userData));
      localStorage.setItem('isSignedUp', 'true');

      alert('Signup successful! Please create your password.');
      window.location.href = '8password.html';
    });
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  function validatePhoneNumber(phone) {
    const re = /^\d{10}$/;
    return re.test(String(phone));
  }

  const createPasswordForm = document.querySelector('form[action="4dashboard.html"]');
  if (createPasswordForm && window.location.pathname.includes('8password.html')) {
    createPasswordForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const newPassword = document.getElementById('createpassword').value;
      const confirmPassword = document.getElementById('confirmpassword').value;

      if (newPassword.length < 8) {
        alert('Password must be at least 8 characters long.');
        return;
      }

      if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
      }

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));
      if (currentUser) {
        currentUser.password = newPassword;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Password created successfully! You can now log in.');
        window.location.href = '2login.html';
      } else {
        alert('Error: User data not found. Please sign up again.');
        window.location.href = '3signup.html';
      }
    });
  }

  const forgotPasswordForm = document.querySelector('form[action="2login.html"]');
  if (forgotPasswordForm && window.location.pathname.includes('9fpassword.html')) {
    forgotPasswordForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const otp = document.getElementById('otp').value;
      const newPassword = document.getElementById('createpassword').value;
      const confirmPassword = document.getElementById('confirmpassword').value;

      if (!otp) {
        alert('Please enter the OTP.');
        return;
      }

      if (newPassword.length < 8) {
        alert('New password must be at least 8 characters long.');
        return;
      }

      if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
      }

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));
      if (currentUser) {
        currentUser.password = newPassword;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Password reset successfully! Please log in with your new password.');
        window.location.href = '2login.html';
      } else {
        alert('Error: User data not found. Please sign up or try again.');
        window.location.href = '3signup.html';
      }
    });
  }

  const loginForm = document.getElementById('loin-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const usernameInput = document.getElementById('username').value;
      const passwordInput = document.getElementById('password').value;

      const currentUser = JSON.parse(localStorage.getItem('currentUser '));

      if (currentUser && currentUser.username === usernameInput && currentUser.password === passwordInput) {
        localStorage.setItem('isLoggedIn', 'true');
        alert('Login successful!');
        window.location.href = '4dashboard.html';
      } else {
        alert('Invalid username or password.');
      }
    });
  }

  const addExpenseForm = document.querySelector('form[action="4dashboard.html"]');
  if (addExpenseForm && window.location.pathname.includes('6expense.html')) {
    addExpenseForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const category = document.getElementById('category').value;
      const customCategory = document.getElementById('custom_category').value;
      const amount = parseFloat(addExpenseForm.querySelector('input[name="rupees"]').value);
      const quantity = parseInt(addExpenseForm.querySelector('input[name="quantity"]').value);
      const description = addExpenseForm.querySelector('input[name="description"]').value;
      const date = addExpenseForm.querySelector('input[name="date"]').value;

      if (!category || amount <= 0 || quantity <= 0 || !date) {
        alert('Please fill in all required fields with valid values.');
        return;
      }

      const finalCategory = category === 'Other' ? customCategory : category;

      const transaction = {
        type: 'expense',
        category: finalCategory,
        amount: amount,
        quantity: quantity,
        description: description,
        date: date
      };

      let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
      transactions.push(transaction);
      localStorage.setItem('transactions', JSON.stringify(transactions));

      alert('Expense added successfully!');
      window.location.href = '4dashboard.html';
    });
  }

  const addIncomeForm = document.querySelector('form[action="4dashboard.html"]');
  if (addIncomeForm && window.location.pathname.includes('7income.html')) {
    addIncomeForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const category = document.getElementById('income_category').value;
      const amount = parseFloat(document.getElementById('income_rupees').value);
      const description = document.getElementById('income_description').value;
      const date = document.getElementById('income_date').value;

      if (!category || amount <= 0 || !date) {
        alert('Please fill in all required fields with valid values.');
        return;
      }

      const transaction = {
        type: 'income',
        category: category,
        amount: amount,
        description: description,
        date: date
      };

      let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
      transactions.push(transaction);
      localStorage.setItem('transactions', JSON.stringify(transactions));

      alert('Income added successfully!');
      window.location.href = '4dashboard.html';
    });
  }

  if (pathLower.includes('4dashboard.html')) {
    const welcomeText = document.querySelector('.welcome-text');
    const totalExpenseCell = document.querySelector('#total_expense');
    const totalIncomeCell = document.querySelector('#total_income');
    const totalBalanceCell = document.querySelector('#balance');
    const lastUpdated = document.querySelector('.last-updated em');

    async function loadDashboardSummary() {
      try {
        const summary = await fetchJson(endpoints.dashboard);
        if (totalExpenseCell) totalExpenseCell.textContent = formatCurrency(summary.total_expense);
        if (totalIncomeCell) totalIncomeCell.textContent = formatCurrency(summary.total_income);
        if (totalBalanceCell) totalBalanceCell.textContent = formatCurrency(summary.balance);
        if (lastUpdated) {
          const now = new Date();
          lastUpdated.textContent = `Last updated: ${now.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' })}`;
        }
      } catch (error) {
        console.error('Failed to load dashboard summary:', error);
        if (totalExpenseCell) totalExpenseCell.textContent = '—';
        if (totalIncomeCell) totalIncomeCell.textContent = '—';
        if (totalBalanceCell) totalBalanceCell.textContent = '—';
        if (lastUpdated) lastUpdated.textContent = 'Unable to load summary.';
      }
    }

    loadDashboardSummary();
  }

  if (pathLower.includes('13histroy.html')) {
    const tableBody = document.querySelector('.table-section tbody');
    const chartElements = {
      pie: document.getElementById('expenseIncomePieChart'),
      bar: document.getElementById('categoryBarChart'),
    };
    const chartInstances = {
      pie: null,
      bar: null,
    };

    function destroyChart(key) {
      if (chartInstances[key]) {
        chartInstances[key].destroy();
        chartInstances[key] = null;
      }
    }

    function showChartMessage(canvas, message) {
      if (!canvas || !canvas.parentElement) return;
      canvas.style.display = 'none';
      let msg = canvas.parentElement.querySelector('.no-chart-msg');
      if (!msg) {
        msg = document.createElement('div');
        msg.className = 'no-chart-msg';
        canvas.parentElement.appendChild(msg);
      }
      msg.textContent = message;
    }

    function hideChartMessage(canvas) {
      if (!canvas || !canvas.parentElement) return;
      canvas.style.display = 'block';
      const msg = canvas.parentElement.querySelector('.no-chart-msg');
      if (msg) {
        msg.remove();
      }
    }

    function createChart(key, config) {
      const canvas = chartElements[key];
      if (!canvas) return;
      destroyChart(key);
      hideChartMessage(canvas);
      chartInstances[key] = new Chart(canvas.getContext('2d'), config);
    }

    function formatDateTime(value) {
      if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value.trim())) {
        return escapeHtml(value);
      }
      const parsed = new Date(value);
      return Number.isNaN(parsed.valueOf())
        ? escapeHtml(value)
        : parsed.toLocaleString();
    }

    function renderPieChart(rows) {
      const canvas = chartElements.pie;
      if (!canvas) return;

      const expenses = {};
      const incomes = {};

      rows.forEach((row) => {
        const category = row.category || 'Uncategorized';
        const amount = Number(row.amount || 0);
        const type = (row.type || '').toLowerCase();
        if (type === 'expense') {
          expenses[category] = (expenses[category] || 0) + amount;
        } else if (type === 'income') {
          incomes[category] = (incomes[category] || 0) + amount;
        }
      });

      const labels = [...Object.keys(expenses), ...Object.keys(incomes)];
      const values = [...Object.values(expenses), ...Object.values(incomes)];

      if (!labels.length) {
        destroyChart('pie');
        showChartMessage(canvas, 'No chart data available');
        return;
      }

      createChart('pie', {
        type: 'pie',
        data: {
          labels,
          datasets: [{
            label: 'Amount (₹)',
            data: values,
            backgroundColor: [
              '#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff',
              '#ff9f40', '#8dd3c7', '#80b1d3', '#fdb462', '#b3de69',
            ],
          }],
        },
        options: {
          responsive: true,
          plugins: { legend: { position: 'bottom' } },
        },
      });
    }

    function renderCategoryBarChart(rows) {
      const canvas = chartElements.bar;
      if (!canvas) return;

      const expenseTotals = {};
      const incomeTotals = {};

      rows.forEach((row) => {
        const category = row.category || 'Uncategorized';
        const amount = Number(row.amount || 0);
        const type = (row.type || '').toLowerCase();
        if (type === 'expense') {
          expenseTotals[category] = (expenseTotals[category] || 0) + amount;
        } else if (type === 'income') {
          incomeTotals[category] = (incomeTotals[category] || 0) + amount;
        }
      });

      const categories = Array.from(new Set([
        ...Object.keys(expenseTotals),
        ...Object.keys(incomeTotals),
      ])).sort((a, b) => a.localeCompare(b));

      if (!categories.length) {
        destroyChart('bar');
        showChartMessage(canvas, 'No category data available');
        return;
      }

      const expenseData = categories.map((cat) => expenseTotals[cat] || 0);
      const incomeData = categories.map((cat) => incomeTotals[cat] || 0);

      createChart('bar', {
        type: 'bar',
        data: {
          labels: categories,
          datasets: [
            {
              label: 'Expense',
              data: expenseData,
              backgroundColor: '#ff6384',
            },
            {
              label: 'Income',
              data: incomeData,
              backgroundColor: '#36a2eb',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'top' },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => formatCurrency(value),
              },
            },
          },
        },
      });
    }

    function renderHistoryTable(rows) {
      if (!tableBody) return;

      tableBody.innerHTML = '';

      if (!rows.length) {
        tableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
        return;
      }

      rows.forEach((row) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${formatDateTime(row.date)}</td>
          <td>${escapeHtml(row.type)}</td>
          <td>${escapeHtml(row.category)}</td>
          <td style="text-align:right">${formatCurrency(row.amount)}</td>
          <td>${escapeHtml(row.description)}</td>
        `;
        tableBody.appendChild(tr);
      });
    }

    function renderCharts(rows) {
      renderPieChart(rows);
      renderCategoryBarChart(rows);
    }

    async function loadHistory() {
      try {
        const rows = await fetchTransactions();
        renderHistoryTable(rows);
        renderCharts(rows);
      } catch (error) {
        console.error('Failed to load history:', error);
        if (tableBody) {
          tableBody.innerHTML = `<tr><td colspan="5" class="center-text">${escapeHtml(error.message)}</td></tr>`;
        }
        Object.values(chartElements).forEach((canvas) => {
          if (canvas) showChartMessage(canvas, 'Unable to load chart data');
        });
      }
    }

    loadHistory();
    window.reloadTransactions = loadHistory;
  }

  const shouldLoadProfile =
    profileNameTargets.length ||
    profileEmailTargets.length ||
    profileCountryTargets.length ||
    profileGenderTargets.length ||
    profileIncomeTargets.length ||
    profileAvatarTargets.length;

  if (shouldLoadProfile) {
    getProfile().then((user) => {
      if (user) {
        applyProfileData(user);
      }
    });
  }

  const profileUpdateForm = document.querySelector('form[action="../../BACKEND/handle_profile_update.php"]');
  if (profileUpdateForm) {
    getProfile().then((user) => {
      if (!user) return;
      profileUpdateForm.querySelector('input[name="username"]').value = user.username || '';
      profileUpdateForm.querySelector('input[name="email"]').value = user.email || '';
      profileUpdateForm.querySelector('input[name="country"]').value = user.country || '';
      const incomeField = profileUpdateForm.querySelector('#income');
      if (incomeField) incomeField.value = user.monthly_income != null ? Number(user.monthly_income) : '';
    });
  }

  if (pathLower.includes('12downloadexpenses.html')) {
    const tableBody = document.querySelector('.table-section tbody');
    const filterSelect = document.getElementById('duration-filter');
    const downloadForms = document.querySelectorAll('.download-form');
    let currentDuration = filterSelect ? filterSelect.value : '';

    function renderDownloadTable(rows) {
      if (!tableBody) return;

      tableBody.innerHTML = '';
      if (!rows.length) {
        tableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
        return;
      }

      rows.forEach((row) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${escapeHtml(row.date)}</td>
          <td>${escapeHtml(row.type)}</td>
          <td>${escapeHtml(row.category)}</td>
          <td style="text-align:right">${formatCurrency(row.amount)}</td>
          <td>${escapeHtml(row.description)}</td>
        `;
        tableBody.appendChild(tr);
      });
    }

    async function loadDownloadTable() {
      try {
        const rows = await fetchTransactions({ duration: currentDuration });
        renderDownloadTable(rows);
      } catch (error) {
        console.error('Failed to load download data:', error);
        if (tableBody) {
          tableBody.innerHTML = `<tr><td colspan="5" class="center-text">${escapeHtml(error.message)}</td></tr>`;
        }
      }
    }

    function syncDownloadForms() {
      downloadForms.forEach((form) => {
        const durationField = form.querySelector('input[name="duration"]');
        if (durationField) durationField.value = currentDuration || '';
      });
    }

    if (filterSelect) {
      filterSelect.addEventListener('change', () => {
        currentDuration = filterSelect.value;
        syncDownloadForms();
        loadDownloadTable();
      });
    }

    syncDownloadForms();
    loadDownloadTable();
  }

  if (window.location.pathname.includes('14RecieptScanner.html')) {
    const dropArea = document.getElementById('drop-area');
    const fileElem = document.getElementById('fileElem');
    const filePreview = document.getElementById('file-preview');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, unhighlight, false);
    });

    dropArea.addEventListener('drop', handleDrop, false);

    const browseLabel = document.querySelector('.browse-label');
    if (browseLabel) {
      browseLabel.addEventListener('click', () => {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'fileElem';
        fileInput.accept = 'image/*,application/pdf';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        fileInput.addEventListener('change', (event) => {
          handleFiles(event.target.files);
          document.body.removeChild(fileInput);
        });
        fileInput.click();
      });
    }

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    function highlight() {
      dropArea.classList.add('highlight');
    }

    function unhighlight() {
      dropArea.classList.remove('highlight');
    }

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      handleFiles(files);
    }

    function handleFiles(files) {
      files = [...files];
      files.forEach(previewFile);
      alert(`File(s) uploaded: ${files.map(f => f.name).join(', ')} (functionality not fully implemented in this demo).`);
    }

    function previewFile(file) {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend = function () {
        const img = document.createElement('img');
        img.src = reader.result;
        img.style.maxWidth = '100px';
        img.style.maxHeight = '100px';
        img.style.margin = '10px';
        filePreview.appendChild(img);
      };
    }
  }

  if (window.location.pathname.includes('15Savingschemes.html')) {
    const calculateButton = document.querySelector('.saving-schemes-container .form-buttons button');
    if (calculateButton) {
      calculateButton.addEventListener('click', () => {
        alert('Interest calculation is available in the full version. This is a demo.');
      });
    }
  }

  const form = document.querySelector(".transactionForm");
  const typeSelect = document.getElementById("type");

  form.addEventListener("submit", function (e) {
    if (typeSelect.value === "Income") {
      form.action = "../../BACKEND/handle_income.php";
    } else if (typeSelect.value === "Expense") {
      form.action = "../../BACKEND/handle_expense.php";
    } else {
      e.preventDefault(); 
      alert("Please select a type: Income or Expense");
    }
  });
  
}); 