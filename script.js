document.addEventListener('DOMContentLoaded', function() {
	calcolaPunteggi();
    loadTeams();
    loadNazionali();
    loadMoltiplicatori();
	loadListone();
	document.querySelector('.tablink').click();
});

function calcolaPunteggi(){
    fetch('backend.php?action=calcolaPunteggi')
        .then(response => response.json())
		.then(data => {
            console.log('Punteggi calcolati:', data);
        })
        .catch(error => console.error('Errore nel calcolo dei punteggi:', error));
}

function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablink");
    for (i = 0; i < tablinks.length; i++) {
		tablinks[i].classList.replace('selected','unselected');
    }
    document.getElementById(tabName).style.display = "block";
	evt.currentTarget.classList.replace('unselected','selected');
}

function loadTeams() {
    fetch('backend.php?data=teams')
        .then(response => response.json())
        .then(data => {
            let table = document.getElementById('teamsTable').getElementsByTagName('tbody')[0];
            data.forEach(team => {
                let row = table.insertRow();
                let cellExpand = row.insertCell(0);
                let cellGiocatore = row.insertCell(1);
                let cellPunteggio = row.insertCell(2);

                cellExpand.innerHTML = '<button onclick="toggleDetail(this)"><img src="expand_gray.png"></button>';
				cellExpand.classList.add('expander');
                cellGiocatore.textContent = team.giocatore;
                cellPunteggio.textContent = team.punteggio;
				cellPunteggio.classList.add('numeric');
				
                let detailRow = table.insertRow();
                let detailCell = detailRow.insertCell(0);
                detailCell.colSpan = 4;
                
				team.nazionali.sort((a, b) => b.punteggio - a.punteggio);
                
				detailCell.innerHTML = `<div class="detail">
                                            <table class="detailTable">
                                                ${team.nazionali.map(nazionale => `
                                                    <tr>
														<td></td>
                                                        <td class="detailflag"><img src="flags/${nazionale.bandiera}" alt="${nazionale.nome} flag" class="flag"></td>
														<td>${nazionale.nome}</td>
                                                        <td class="numeric">${nazionale.punteggio}</td>
                                                    </tr>
                                                `).join('')}
                                            </table>
                                        </div>`;
				detailRow.style.display = "none";
            });
        });
}

function loadNazionali() {
    fetch('backend.php?data=nazionali')
        .then(response => response.json())
        .then(data => {
            let table = document.getElementById('nazionaliTable').getElementsByTagName('tbody')[0];
            table.innerHTML = ""; // Clear existing rows
            data.forEach(nazionale => {
                let row = table.insertRow();
                let cellExpand = row.insertCell(0);
                let cellBandiera = row.insertCell(1);
				let cellNazionale = row.insertCell(2);
                let cellPunteggio = row.insertCell(3);

                cellExpand.innerHTML = '<button class="toggle" onclick="toggleDetail(this)"><img src="expand_gray.png"></button>';
				cellExpand.classList.add('expander');
				cellBandiera.innerHTML = `<img class="flag" src="flags/${nazionale.flag}" alt="${nazionale.name}">`;
				cellBandiera.classList.add('flag');
				cellNazionale.innerHTML = nazionale.name;
                cellPunteggio.textContent = nazionale.score;
				cellPunteggio.classList.add('numeric');
				
				let detailRow = table.insertRow();
                let detailCell = detailRow.insertCell(0);
                detailCell.colSpan = 4;
				detailCell.innerHTML = `<div class="detail">
                            <table class="detailTable">
                                ${Object.entries(nazionale.details).map(([key, value]) => `
                                    <tr class="${value < 0 ? 'red' : 'green'}">
										<td></td>
										<td></td>
                                        <td>${key}</td>
                                        <td class="numeric">${value}</td>
                                    </tr>
                                `).join('')}
                            </table>
                        </div>`;

                detailRow.style.display = "none";
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadMoltiplicatori() {
    fetch('backend.php?data=moltiplicatori')
        .then(response => response.json())
        .then(data => {
            let table = document.getElementById('moltiplicatoriTable').getElementsByTagName('tbody')[0];
            Object.entries(data[0]).forEach(([key, value]) => {
                let row = table.insertRow();
				row.classList.add(value<0?'red':'green');
                let cellEvento = row.insertCell(0);
                let cellMoltiplicatore = row.insertCell(1);

                cellEvento.textContent = key;
                cellMoltiplicatore.textContent = value;
				cellMoltiplicatore.classList.add('numeric');
				
            });
        });
}

function loadListone() {
    fetch('backend.php?data=listone')
        .then(response => response.json())
        .then(data => {
            let table = document.getElementById('listoneTable').getElementsByTagName('tbody')[0];
            table.innerHTML = ""; // Clear existing rows
            data.forEach(nazionale => {
                let row = table.insertRow();
                let cellBandiera = row.insertCell(0);
				let cellNazionale = row.insertCell(1);
                let cellValore = row.insertCell(2);
				
				cellBandiera.innerHTML = `<img class="flag" src="flags/${nazionale.flag}" alt="${nazionale.name}">`;
				cellBandiera.classList.add('flag');
				cellNazionale.innerHTML = nazionale.name;
                cellValore.textContent = nazionale.valore;
				cellValore.classList.add('numeric');
				row.classList.add(nazionale.valore%20==0?'even':'odd');
				
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function toggleDetail(button) {
    let detailRow = button.parentElement.parentElement.nextElementSibling;
    let detailDiv = detailRow.querySelector('.detail');
	if (detailDiv.style.display === "none") {
        detailDiv.style.display = "block";
        detailRow.style.display = "table-row";
        button.innerHTML = '<img src="reduce_gray.png">';
    } else {
        detailDiv.style.display = "none";
        detailRow.style.display = "none";
        button.innerHTML = '<img src="expand_gray.png">';
    }
}