document.addEventListener('DOMContentLoaded', () => {
  const modeToggle = document.getElementById("modeToggle");
  const themeStylesheet = document.getElementById("themeStylesheet");

  // Load saved mode
  if (localStorage.getItem("theme") === "dark") {
    themeStylesheet.href = "../darkmode.css";
    modeToggle.checked = true;
  }

  modeToggle.addEventListener("change", () => {
    if (modeToggle.checked) {
      themeStylesheet.href = "../darkmode.css";
      localStorage.setItem("theme", "dark");
    } else {
      themeStylesheet.href = "../style.css";
      localStorage.setItem("theme", "light");
    }
  });




  const menuIcon = document.querySelector('.menu-icon');
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

  // Update sidebar profile name/email from backend
  (function updateSidebarProfile() {
    fetch('../../BACKEND/handle_profile.php')
      .then(r => r.ok ? r.json() : null)
      .then(user => {
        if (!user) return;
        const nameEl = document.querySelector('.profile-details a div:nth-child(1)');
        const emailEl = document.querySelector('.profile-details a div:nth-child(2)');
        if (nameEl && user.username) nameEl.textContent = user.username;
        if (emailEl && user.email) emailEl.textContent = user.email;
      })
      .catch(() => {});
  })();

  // Remove localStorage-based signup/login/transaction simulation.

  // Utility fetch helper
  function fetchData(url, options = {}) {
    return fetch(url, { method: options.method || 'GET', headers: { 'Content-Type': 'application/json' }, ...options })
      .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
      .catch(err => { console.error(err); alert('Error loading data: ' + err.message); });
  }

  // Page: Dashboard totals
  if (window.location.pathname.includes('4dashboard.html')) {
    fetchData('../../BACKEND/handle_dashboard.php').then(data => {
      if (!data) return;
      const exp = document.getElementById('total_expense');
      const inc = document.getElementById('total_income');
      const bal = document.getElementById('balance');
      if (exp) exp.textContent = `₹${parseFloat(data.total_expense || 0).toFixed(2)}`;
      if (inc) inc.textContent = `₹${parseFloat(data.total_income || 0).toFixed(2)}`;
      if (bal) bal.textContent = `₹${parseFloat(data.balance || 0).toFixed(2)}`;
    });
  }

  // Page: History table
  if (window.location.pathname.includes('13histroy.html')) {
    fetchData('../../BACKEND/handle_history_fetch.php').then(rows => {
      const tbody = document.querySelector('.table-section tbody');
      if (!tbody) return;
      tbody.innerHTML = '';
      if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
      } else {
        rows.forEach(t => {
          const r = tbody.insertRow();
          r.insertCell(0).textContent = t.date;
          r.insertCell(1).textContent = t.type;
          r.insertCell(2).textContent = t.category;
          r.insertCell(3).textContent = `₹${parseFloat(t.amount).toLocaleString()}`;
          r.insertCell(4).textContent = t.description || '-';
        });
      }

      // Build separate datasets grouped by category for Expense and Income
      if (typeof Chart !== 'undefined') {
        const groupBy = (items, keyFn, valFn) => items.reduce((acc, it) => { const k = keyFn(it); acc[k] = (acc[k] || 0) + valFn(it); return acc; }, {});
        const expenses = rows.filter(r => r.type === 'Expense');
        const incomes  = rows.filter(r => r.type === 'Income');
        const expAgg = groupBy(expenses, r => r.category, r => parseFloat(r.amount || 0));
        const incAgg = groupBy(incomes,  r => r.category, r => parseFloat(r.amount || 0));

        // Nice color palette
        const palette = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#8DD17E','#C49C94','#F7A1C4','#7FB3D5'];
        const makeColors = (n) => Array.from({length:n}, (_,i)=> palette[i % palette.length]);

        const expLabels = Object.keys(expAgg);
        const expData   = Object.values(expAgg);
        const incLabels = Object.keys(incAgg);
        const incData   = Object.values(incAgg);

        const expCanvas = document.getElementById('expenseChart');
        const incCanvas = document.getElementById('incomeChart');
        if (expCanvas) {
          const ctx = expCanvas.getContext('2d');
          new Chart(ctx, { type: 'pie', data: { labels: expLabels, datasets: [{ data: expData, backgroundColor: makeColors(expData.length) }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }
        if (incCanvas) {
          const ctx = incCanvas.getContext('2d');
          new Chart(ctx, { type: 'pie', data: { labels: incLabels, datasets: [{ data: incData, backgroundColor: makeColors(incData.length) }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }
      }
    });
  }

  // Page: Profile details
  if (window.location.pathname.includes('10profile.html')) {
    fetchData('../../BACKEND/handle_profile.php').then(user => {
      if (!user) return;
      const boxes = document.querySelectorAll('.profile-detail-box');
      if (boxes[0]) boxes[0].textContent = `Username: ${user.username || ''}`;
      if (boxes[1]) boxes[1].textContent = `Email: ${user.email || ''}`;
      if (boxes[2]) boxes[2].textContent = `Country: ${user.country || ''}`;
      if (boxes[3]) boxes[3].textContent = `Gender: ${user.gender || ''}`;
      const img = document.querySelector('.img-section img');
      if (img && user.profile_pic_path) img.src = user.profile_pic_path;
    });
  }

  // Page: Download preview via backend
  if (window.location.pathname.includes('12DownloadExpenses.html')) {
    const filterSelect = document.getElementById('duration-filter');
    const tbody = document.querySelector('.table-section tbody');
    const loadFiltered = (duration = '') => {
      const params = new URLSearchParams({ format: 'json', duration });
      fetchData(`../../BACKEND/handle_download.php?${params.toString()}`).then(rows => {
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
          return;
        }
        rows.forEach(t => {
          const r = tbody.insertRow();
          r.insertCell(0).textContent = t.date;
          r.insertCell(1).textContent = t.type;
          r.insertCell(2).textContent = t.category;
          r.insertCell(3).textContent = `₹${parseFloat(t.amount).toLocaleString()}`;
          r.insertCell(4).textContent = t.description || '-';
        });
      });
    };
    if (filterSelect && tbody) {
      filterSelect.addEventListener('change', e => loadFiltered(e.target.value));
      loadFiltered();
    }
  }

  // Global alerts from query params
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('error')) alert('Error: ' + decodeURIComponent(urlParams.get('error')));
  if (urlParams.get('success')) alert('Success: ' + decodeURIComponent(urlParams.get('success')));
  window.history.replaceState({}, document.title, window.location.pathname);

  if (window.location.pathname.includes('13histroy.html')) {
    const historyTableBody = document.querySelector('.table-section tbody');
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];

    if (historyTableBody) {
      historyTableBody.innerHTML = '';

      if (transactions.length === 0) {
        historyTableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
      } else {
        transactions.forEach(transaction => {
          const row = historyTableBody.insertRow();
          const typeClass = transaction.type === 'expense' ? 'expense-row' : 'income-row';
          row.classList.add(typeClass);

          row.insertCell().textContent = transaction.date;
          row.insertCell().textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
          row.insertCell().textContent = transaction.category;
          row.insertCell().textContent = `₹${transaction.amount.toLocaleString()}`;
          row.insertCell().textContent = transaction.description || '-';
        });
      }
    }

    const chartSection = document.querySelector('.chart-section');
    if (chartSection) {
      chartSection.innerHTML = '<canvas id="expenseIncomeChart"></canvas>';
      const ctx = document.getElementById('expenseIncomeChart');

      if (ctx) {
        const expenseCategories = {};
        const incomeCategories = {};

        transactions.forEach(transaction => {
          if (transaction.type === 'expense') {
            expenseCategories[transaction.category] = (expenseCategories[transaction.category] || 0) + transaction.amount;
          } else if (transaction.type === 'income') {
            incomeCategories[transaction.category] = (incomeCategories[transaction.category] || 0) + transaction.amount;
          }
        });

        const expenseLabels = Object.keys(expenseCategories);
        const expenseData = Object.values(expenseCategories);

        const incomeLabels = Object.keys(incomeCategories);
        const incomeData = Object.values(incomeCategories);

        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: [...expenseLabels, ...incomeLabels],
            datasets: [{
              label: 'Amount (₹)',
              data: [...expenseData, ...incomeData],
              backgroundColor: [
                'rgba(255, 99, 132, 0.8)', 'rgba(255, 159, 64, 0.8)', 'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)'
              ],
              borderColor: [
                'rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)', 'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)'
              ],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: 'Expense and Income Distribution by Category'
              }
            }
          }
        });
      }
    }
  }

  if (window.location.pathname.includes('10profile.html')) {
    const profileImg = document.querySelector('.profile-container .img-section img');
    const profileDetails = document.querySelectorAll('.profile-detail-box');
    const currentUser = JSON.parse(localStorage.getItem('currentUser '));

    if (currentUser) {
      if (profileImg && currentUser.profilePic) {
        profileImg.src = currentUser.profilePic;
      }
      if (profileDetails.length >= 4) {
        profileDetails[0].textContent = `Username: ${currentUser.username || ''}`;
        profileDetails[1].textContent = `Email-id: ${currentUser.email || ''}`;
        profileDetails[2].textContent = `Country: ${currentUser.country || ''}`;
        profileDetails[3].textContent = `Gender: ${currentUser.gender || ''}`;
      }
    }
  }

  const updateProfileForm = document.querySelector('form[action="10profile.html"]');
  if (updateProfileForm) {
    const currentUser = JSON.parse(localStorage.getItem('currentUser '));
    if (currentUser) {
      updateProfileForm.querySelector('input[name="username"]').value = currentUser.username || '';
      updateProfileForm.querySelector('input[name="email"]').value = currentUser.email || '';
      updateProfileForm.querySelector('input[name="country"]').value = currentUser.country || '';
      updateProfileForm.querySelector('#income').value = currentUser.monthlyIncome || '';
    }

    updateProfileForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const newUsername = updateProfileForm.querySelector('input[name="username"]').value;
      const newEmail = updateProfileForm.querySelector('input[name="email"]').value;
      const newCountry = updateProfileForm.querySelector('input[name="country"]').value;
      const newMonthlyIncome = parseFloat(updateProfileForm.querySelector('#income').value);

      if (!newUsername || !newEmail || !newCountry || isNaN(newMonthlyIncome)) {
        alert('Please fill in all fields correctly.');
        return;
      }

      if (!validateEmail(newEmail)) {
        alert('Please enter a valid email address.');
        return;
      }

      if (currentUser) {
        currentUser.username = newUsername;
        currentUser.email = newEmail;
        currentUser.country = newCountry;
        currentUser.monthlyIncome = newMonthlyIncome;
        localStorage.setItem('currentUser ', JSON.stringify(currentUser));
        alert('Profile updated successfully!');
        window.location.href = '10profile.html';
      } else {
        alert('Error: User data not found.');
      }
    });
  }

  if (window.location.pathname.includes('12DownloadExpenses.html')) {
    const downloadTableBody = document.querySelector('.table-section tbody');
    const filterSelect = document.getElementById('duration-filter');
    let allTransactions = JSON.parse(localStorage.getItem('transactions')) || [];

    const renderTable = (transactionsToDisplay) => {
      if (downloadTableBody) {
        downloadTableBody.innerHTML = '';

        if (transactionsToDisplay.length === 0) {
          downloadTableBody.innerHTML = '<tr><td colspan="5" class="center-text">No transactions found</td></tr>';
        } else {
          transactionsToDisplay.forEach(transaction => {
            const row = downloadTableBody.insertRow();
            row.insertCell().textContent = transaction.date;
            row.insertCell().textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
            row.insertCell().textContent = transaction.category;
            row.insertCell().textContent = `₹${transaction.amount.toLocaleString()}`;
            row.insertCell().textContent = transaction.description || '-';
          });
        }
      }
    };

    const filterTransactions = () => {
      const duration = filterSelect.value;
      let filtered = [];
      const now = new Date();

      if (duration === '') {
        filtered = allTransactions;
      } else {
        filtered = allTransactions.filter(transaction => {
          const transactionDate = new Date(transaction.date);
          switch (duration) {
            case 'day':
              return transactionDate.toDateString() === now.toDateString();
            case 'week':
              const firstDayOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
              const lastDayOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6));
              return transactionDate >= firstDayOfWeek && transactionDate <= lastDayOfWeek;
            case 'month':
              return transactionDate.getMonth() === now.getMonth() && transactionDate.getFullYear() === now.getFullYear();
            case 'year':
              return transactionDate.getFullYear() === now.getFullYear();
            default:
              return true;
          }
        });
      }
      renderTable(filtered);
    };

    filterSelect.addEventListener('change', filterTransactions);
    renderTable(allTransactions);

    const downloadPdfButton = document.querySelector('.download a[download="transaction_history.pdf"]');
    const downloadCsvButton = document.querySelector('.download a[download="transaction_history.csv"]');

    if (downloadPdfButton) {
      downloadPdfButton.addEventListener('click', (event) => {
        event.preventDefault();
        alert('PDF download initiated (functionality not fully implemented in this demo).');
      });
    }

    if (downloadCsvButton) {
      downloadCsvButton.addEventListener('click', (event) => {
        event.preventDefault();
        alert('CSV download initiated (functionality not fully implemented in this demo).');
        const headers = ["Date", "Type", "Category", "Amount", "Description"];
        const rows = allTransactions.map(t => [t.date, t.type, t.category, t.amount, t.description]);
        let csvContent = "data:text/csv;charset=utf-8,"
          + headers.join(",") + "\n"
          + rows.map(e => e.join(",")).join("\n");

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "transactions.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
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