    var nb="_TOTAL_";
    var asInitVals = new Array();
    var base_url = window.location.toString();
	var tabUrl = base_url.split("public");
	
	$(function(){
		setTimeout(function() {
			infoBulle();
		}, 1000);
	});

	
	function infoBulle(){
	
		/***
		 * INFO BULLE DE LA LISTE
		 */

		var tooltips = $( 'table tbody tr td infoBulleVue' ).tooltip({show: {effect: 'slideDown', delay: 250}});
		tooltips.tooltip( 'close' );
		$('table tbody tr td infoBulleVue').mouseenter(function(){
			var tooltips = $( 'table tbody tr td infoBulleVue' ).tooltip({show: {effect: 'slideDown', delay: 250}});
			tooltips.tooltip( 'open' );
		});
	}
	
    var oTable;
    function initialisation()
    {
    	 $( "#accordions" ).accordion();
    	 $( "button" ).button();
    	    
    	 oTable = $('#patient').dataTable
    	 ( {
    	
    		 "sPaginationType": "full_numbers",
    		 "aLengthMenu": [5,7,10,15],
    		 "aaSorting": [],
    		 "oLanguage": {
    			 "sInfo": "_START_ &agrave; _END_ sur _TOTAL_ patients",
    			 "sInfoEmpty": "0 &eacute;l&eacute;ment &agrave; afficher",
    			 "sInfoFiltered": "",
    			 "sUrl": "",
    			 "oPaginate": {
    				 "sFirst":    "|<",
    				 "sPrevious": "<",
    				 "sNext":     ">",
    				 "sLast":     ">|"
    			 }
    		 },

    		 "sAjaxSource":  tabUrl[0]+"public/biologiste/liste-resultats-valides-ajax",
    		 "fnDrawCallback": function() 
    	
    		 {
    			 clickRowHandler();
    		 }
    	 } );
    	
     var asInitVals = new Array();
	
	//le filtre du select
	$('#filter_statut').change(function() 
	{					
		oTable.fnFilter( this.value );
	});
	
	$("tfoot input").keyup( function () {
		/* Filter on the column (the index) of this element */
		oTable.fnFilter( this.value, $("tfoot input").index(this) );
	} );
	
	/*
	 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
	 * the footer
	 */
	$("tfoot input").each( function (i) {
		asInitVals[i] = this.value;
	} );
	
	$("tfoot input").focus( function () {
		if ( this.className == "search_init" )
		{
			this.className = "";
			this.value = "";
		}
	} );
	
	$("tfoot input").blur( function (i) {
		if ( this.value == "" )
		{
			this.className = "search_init";
			this.value = asInitVals[$("tfoot input").index(this)];
		}
	} );

    $(".boutonTerminer").html('<button type="submit" id="terminer" style=" font-family: police2; font-size: 17px; font-weight: bold;"> Terminer </button>');

   }
 
   function clickRowHandler() 
   {
    	var id;
    	$('#patient tbody tr').contextmenu({
    		target: '#context-menu',
    		onItem: function (context, e) {
    			
    			if($(e.target).text() == 'Visualiser' || $(e.target).is('#visualiserCTX')){
    				visualiser(id);
    			} else 
    				if($(e.target).text() == 'Analyses validées' || $(e.target).is('#analysesValideesCTX')){
    					listeAnalysesValidees(id);
    				}
    			
    		}
    	
    	}).bind('mousedown', function (e) {
    			var aData = oTable.fnGetData( this );
    		    id = aData[8];
    	});
    	
    	
    	
    	$("#patient tbody tr").bind('dblclick', function (event) {
    		var aData = oTable.fnGetData( this );
    		var id = aData[8];
    		//visualiser(id);
    	});
    	
    	$('a,img,hass').tooltip({ animation: true, html: true, placement: 'bottom', show: { effect: 'slideDown', } });
    }
    
    function visualiser(id)
    {
    	var cle = id;
        var chemin = tabUrl[0]+'public/biologiste/infos-patient';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data: $(this).serialize(),  
            data:'id='+cle,
            success: function(data) {
            	     var result = jQuery.parseJSON(data);  
            	     
            	     $('#vue_patient').html(result);
            	     $('#contenu').fadeOut(function(){
            	    	 $('#titre span').html('ETAT CIVIL DU PATIENT'); 
            	    	 $('#vue_patient').fadeIn();
            	     });
            	     
            	     $('#terminer').click(function(){
            	    	 $('#vue_patient').fadeOut(function(){
            	    		 $('#titre span').html('LISTE DES RESULTATS PAR PATIENT'); 
                	    	 $('#contenu').fadeIn();
                	    	 $('#vue_patient').html("");
                	     });
            	     });
            }
        
        });
    	
    }

    
    function listeAnalysesValidees(idpatient){
    	
        var chemin = tabUrl[0]+'public/biologiste/get-informations-resultats-analyses-validees-patient';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data:'idpatient='+idpatient,
            success: function(data) {
            	     var result = jQuery.parseJSON(data); 
            	     $('#info_patient').html(result[0]);
            	     $('#listeAnalysesParDemande').html(result[1]);
            	     $('.visualiser'+result[2]).html("<img style='padding-left: 3px; ' src='../images_icons/transfert_droite.png' />");
            	     $('.dateAffichee_'+result[2]).css({'color' : 'green'});
            	     
            	     $('#contenu').fadeOut(function(){
            	    	 $('#titre span').html('LES RESULTATS DES ANALYSES'); 
            	    	 $('#liste_demandes').fadeIn(100);
            	    	 
            	    	 
            	    	 $("#iconeTimeLineDigramme").html("<img style='position: absolute; left: -215px; top: 235px; width: 30px; height: 30px; cursor: pointer;' title='Frise chronologique' onclick='getTimeLineDigramme()' src='../images_icons/icons8-chronologie-30-1.png' />");
            	    	 $("#iconeInfosPathologiesMonsieurVCM").html('<table align="center" style="position: absolute; width: 200px; left: -215px; top: 280px; "> <tr> <td style="margin-top: 35px; text-align: center;"> Chargement </td> </tr>  <tr> <td align="center" style="text-align: center;"> <img style="margin-top: 13px; width: 40px; height: 40px;" src="../images/loading/Chargement_1.gif" /> </td> </tr><table>');
            	    	 //Appel de monsieur VCM
            	    	 getInfosSurMonsieurVCM(idpatient, result[2]);
            	     });
        	            	 
            	     
            	     $('.boutonTerminer').click(function(){
            	    	 $('#liste_demandes').fadeOut(function(){
            	    		 $('#titre span').html('LISTE DES RESULTATS PAR PATIENT'); 
            	    		 $('#contenu').fadeIn(300);
            	    	 });
            	    	 
            	    	 //Renvoi de monsieur VCM
            	    	 $("#iconeMonsieurVCM").toggle(false);	
            	    	 $("#iconeInfosPathologiesMonsieurVCM, #iconeTimeLineDigramme").html("");
            	     });
            	     
            }
        });
    	
    }
    
    
    
    function getInfosSurMonsieurVCM(idpatient, iddemande){
    	
    	var chemin = tabUrl[0]+'public/biologiste/get-informations-vcm';
    	$.ajax({
    		type: 'POST',
    		url: chemin ,
    		data: {'idpatient':idpatient, 'iddemande':iddemande},
    		success: function(data) {
    			var result = jQuery.parseJSON(data);
    			var entreResultat = 0;
    			var lienInfosPathologie = "";
    			var infosPathologieMvcm = new Array();
    			var listeIdAnalyse = new Array();
    			var listeInfosIdAnalyse = new Array();
    			
    			for(var i=0; i<result.length; i++){
    				entreResultat = 1;
    				var idanalyse = result[i][0];
    				var libPathologie = result[i][1];
    				var infosLien = result[i][2];
    				var infosResult = result[i][3];
    				var idDemande = result[i][4];
    				listeIdAnalyse[i] = idanalyse;
    				listeInfosIdAnalyse[idanalyse] = new Array(); 
    				listeInfosIdAnalyse[idanalyse][0] = idDemande;
    				listeInfosIdAnalyse[idanalyse][1] = idanalyse;
    				listeInfosIdAnalyse[idanalyse][2] = infosResult;
    				listeInfosIdAnalyse[idanalyse][3] = libPathologie;
    				
    				if(i==0){ lienInfosPathologie += infosLien; }else{ lienInfosPathologie += '___VCM:'+infosLien; }
    				infosPathologieMvcm[i] = new Array();
    				infosPathologieMvcm[i][0] = idanalyse;
    				infosPathologieMvcm[i][1] = libPathologie;
    				infosPathologieMvcm[i][2] = infosResult;
    				infosPathologieMvcm[i][3] = idDemande;
    			}

    			
    			if(entreResultat == 0){
        			$("#iconeMonsieurVCM").toggle(true);
        			$("#iconeInfosPathologiesMonsieurVCM").html("");
    			}else{
    				var monsieurVcm = getMonsieurVCM(lienInfosPathologie);
    				var scriptVcmFinal = getPathologiesMonsieurVcm(infosPathologieMvcm, listeIdAnalyse, listeInfosIdAnalyse);
    				monsieurVcm += scriptVcmFinal;
    				
    				//alert(monsieurVcm);
    				$('#iconeInfosPathologiesMonsieurVCM').html(monsieurVcm);
    			}
    		}
    	});
    	
    }
    
    
    function getMonsieurVCM(lienInfosPathologie){
    	
    	var scritpMVCM = ''+
				         '<div style="position: absolute; width: 200px; left: -215px; top: 280px; background: white; border: 1.5px solid #dcdcdc; box-shadow: 0 1px 2px rgba(0,0,0,0.3); border-radius: 10px;">'+
				           '<img style="border-radius: 10px;" src="http://localhost:5000/appliweb/vcm/mvcm/VCM:'+lienInfosPathologie+'.svg?width=200" usemap="#imagemap" />'+
				         '</div>';
        
    	return scritpMVCM;
    }
    
    
    function getPathologiesMonsieurVcm(infosPathologieMvcm, listeIdAnalyse, listeInfosIdAnalyse){
    	
    	var scriptImageMap = '<map name="imagemap">';
    	var scriptTextMap = ''+
    	                    '<script>'+
							//'if (typeof(window["hideables"]) == "undefined") {'+
							   'hideables = new Array();'+
							//'}'+
						    '</script>';
    	
    	
    	for(var i=0 ; i<infosPathologieMvcm.length ; i++){
    		
    		if(infosPathologieMvcm[i][0] == 71){ //130, 49, 151, 80 : coords de l'icone globule rouge
        		
    			var compInfos1 = "";
    			var compInfos2 = "";
    			var compInfoResutat = -1;
    			var tabInfosAnalyse = new Array();
    			if(listeIdAnalyse.indexOf(10) != -1){
    				compInfos1 = ", Paludisme"; 
    				compInfos2 = "Paludisme";
    				compInfoResutat = listeInfosIdAnalyse[10][0];
    				
    				tabInfosAnalyse[0] = listeInfosIdAnalyse[10][0];
    				tabInfosAnalyse[1] = listeInfosIdAnalyse[10][1];
    				tabInfosAnalyse[2] = listeInfosIdAnalyse[10][2];
    			}else 
    				if(listeIdAnalyse.indexOf(21) != -1){
    					compInfos1 = ", "+listeInfosIdAnalyse[21][3]; 
        				compInfos2 = listeInfosIdAnalyse[21][3];
        				compInfoResutat = listeInfosIdAnalyse[21][0];
        				
        				tabInfosAnalyse[0] = listeInfosIdAnalyse[21][0];
        				tabInfosAnalyse[1] = listeInfosIdAnalyse[21][1];
        				tabInfosAnalyse[2] = listeInfosIdAnalyse[21][2];
    				}
    			
    			
    			scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="130, 49, 151, 80" title="'+infosPathologieMvcm[i][1]+''+compInfos1+'" />';
    			
        		scriptTextMap +=  ''+
					    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-63px; top:345px; width:80px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
								  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:325px; border-style:solid; border-color:black; border-width:1px; width: 520px; border-radius: 10px;">'+
								    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
								    '<div style="margin:8px;">'+
								      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>';  
				
        		//Lorsqu'il y a aussi un cas de paludisme (Goutte �paisse)
        		if(compInfoResutat != -1){
					scriptTextMap += '<b> <br/> '+compInfos2+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+tabInfosAnalyse[2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+compInfoResutat+')"> &#128065; </span>';
				}				    
								      
		        scriptTextMap +=	'</div>'+
								  '</div>';
								  
			    //Lorsqu'il y a aussi un cas de paludisme (Goutte �paisse)
        		if(listeIdAnalyse.indexOf(10) != -1){
        			scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+tabInfosAnalyse[1]+'\').style.display=\'block\';document.getElementById(\'box_'+tabInfosAnalyse[1]+'\').style.display=\'block\'" shape="rect" coords="103,113,133,134" title="Paludisme (infection parasitaire des globules rouges)" />';
					scriptTextMap  += ''+
						    		  '<div id="fleche_10" style="display:none; position:absolute; left:-80px; top:405px; width:100px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
									  '<div id="box_10" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:390px; border-style:solid; border-color:black; border-width:1px; width: 520px; border-radius: 10px;">'+
									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
									    '<div style="margin:8px;">'+
									      '<b> Paludisme </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * infection parasitaire des globules rouges <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+compInfoResutat+')"> &#128065; </span>'+  
					                    '</div>'+
					                  '</div>';
					scriptTextMap +=  '<script>'+
										'hideables.push("fleche_'+tabInfosAnalyse[1]+'");'+
										'hideables.push("box_'+tabInfosAnalyse[1]+'");'+
									  '</script>';
				}	
								  
								  
        		scriptTextMap +=  '<script>'+
									'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
									'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
								  '</script>';	
								  
    		}else
    			if(infosPathologieMvcm[i][0] == 10 && listeIdAnalyse.indexOf(71) == -1){ //130, 49, 151, 80 : coords de l'icone globule rouge
    				
    				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="130, 49, 151, 80" title="'+infosPathologieMvcm[i][1]+'" />';
    				
    				scriptTextMap +=  ''+
						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-63px; top:340px; width:80px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:320px; border-style:solid; border-color:black; border-width:1px; width: 520px; border-radius: 10px;">'+
									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
									    '<div style="margin:8px;">'+
									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
    									'</div>'+
				  					  '</div>';
    				
    				scriptTextMap +=  '<script>'+
										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
									  '</script>';	
    			
    			}else
    				if(infosPathologieMvcm[i][0] == 53){ //0, 173, 21, 203 : coords de l'icone inflammation
        				
        				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="0, 173, 21, 203" title="'+infosPathologieMvcm[i][1]+'" />';
        				
        				scriptTextMap +=  ''+
    						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-193px; top:470px; width:210px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
    									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:450px; border-style:solid; border-color:black; border-width:1px; width: 200px; border-radius: 10px;">'+
    									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
    									    '<div style="margin:8px;">'+
    									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
        									'</div>'+
    				  					  '</div>';
        				
        				scriptTextMap +=  '<script>'+
    										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
    										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
    									  '</script>';	
        			
        			}else
        				if(infosPathologieMvcm[i][0] == 54){ //152,	76,	173, 97 : coords de l'icone articulation
            				
            				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="152, 76, 173, 97" title="'+infosPathologieMvcm[i][1]+'" />';
            				
            				scriptTextMap +=  ''+
        						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-40px; top:370px; width:80px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
        									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:350px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
        									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
        									    '<div style="margin:8px;">'+
        									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
            									'</div>'+
        				  					  '</div>';
            				
            				scriptTextMap +=  '<script>'+
        										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
        										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
        									  '</script>';	
            			
            			}else
            				if(infosPathologieMvcm[i][0] == 25){ //130, 54, 151, 75 : coords de l'icone risque de trouble de la lipidémie
                				
                				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="130, 54, 151, 75" title="'+infosPathologieMvcm[i][1]+'" />';
                				
                				scriptTextMap +=  ''+
            						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-63px; top:350px; width:80px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
            									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:330px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
            									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
            									    '<div style="margin:8px;">'+
            									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                									'</div>'+
            				  					  '</div>';
                				
                				scriptTextMap +=  '<script>'+
            										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
            										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
            									  '</script>';	
                			
                			}else
                				if(infosPathologieMvcm[i][0] == 37){ //14, 109, 35, 130 : coords de l'icone maladie hépatique
                    				
                    				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="14, 109, 35, 130" title="'+infosPathologieMvcm[i][1]+'" />';
                    				
                    				scriptTextMap +=  ''+
                						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-179px; top:400px; width:205px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
                									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:385px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
                									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
                									    '<div style="margin:8px;">'+
                									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                    									'</div>'+
                				  					  '</div>';
                    				
                    				scriptTextMap +=  '<script>'+
                										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
                										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
                									  '</script>';	
                    			
                    			}else
                    				if(infosPathologieMvcm[i][0] == 21 && listeIdAnalyse.indexOf(71) == -1){ //130, 49, 151, 80 : coords de l'icone glyc�mie
                        				
                        				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="130, 49, 151, 80" title="'+infosPathologieMvcm[i][1]+'" />';
                        				
                        				scriptTextMap +=  ''+
                    						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-63px; top:345px; width:80px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
                    									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:325px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
                    									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
                    									    '<div style="margin:8px;">'+
                    									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                        									'</div>'+
                    				  					  '</div>';
                        				
                        				scriptTextMap +=  '<script>'+
                    										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
                    										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
                    									  '</script>';	
                        			
                        			}else
                        				if(infosPathologieMvcm[i][0] == 22){ //60, 110, 81, 141 : coords de l'icone rein
                            				
                            				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="60, 110, 81, 141" title="'+infosPathologieMvcm[i][1]+'" />';
                            				
                            				scriptTextMap +=  ''+
                        						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-135px; top:400px; width:160px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
                        									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:385px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
                        									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
                        									    '<div style="margin:8px;">'+
                        									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                            									'</div>'+
                        				  					  '</div>';
                            				
                            				scriptTextMap +=  '<script>'+
                        										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
                        										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
                        									  '</script>';	
                            			
                            			}else
                            				if(infosPathologieMvcm[i][0] == 51){ //14, 62, 38, 86 : coords de l'icone beta hcg (Grossesse)
                                				
                                				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="14, 62, 38, 86" title="'+infosPathologieMvcm[i][1]+'" />';
                                				
                                				scriptTextMap +=  ''+
                            						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-176px; top:353px; width:200px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
                            									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:338px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
                            									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
                            									    '<div style="margin:8px;">'+
                            									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                                									'</div>'+
                            				  					  '</div>';
                                				
                                				scriptTextMap +=  '<script>'+
                            										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
                            										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
                            									  '</script>';	
                                			
                                			}else
                                				if(infosPathologieMvcm[i][0] == 52){ //37, 131, 58, 152 : coords de l'icone PSA (Prostate)
                                    				
                                    				scriptImageMap += '<area onClick="hide_all();document.getElementById(\'fleche_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\';document.getElementById(\'box_'+infosPathologieMvcm[i][0]+'\').style.display=\'block\'" shape="rect" coords="37, 131, 58, 152" title="'+infosPathologieMvcm[i][1]+'" />';
                                    				
                                    				scriptTextMap +=  ''+
                                						    		  '<div id="fleche_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; left:-157px; top:422px; width:200px; border-top-style:solid; border-top-color:black; border-top-width:1.5px;"></div>'+
                                									  '<div id="box_'+infosPathologieMvcm[i][0]+'" style="display:none; position:absolute; background-color:#FFFFBB; left:15px; top:407px; border-style:solid; border-color:black; border-width:1px; width: 330px; border-radius: 10px;">'+
                                									    '<div onClick="hide_all()" style="float: right; border-right-style:none; border-top-style:none; border-bottom-style:solid; border-left-style:solid; border-color:black; border-width:1px; cursor:pointer;"> <b>&nbsp;X&nbsp;</b> </div>'+
                                									    '<div style="margin:8px;">'+
                                									      '<b> '+infosPathologieMvcm[i][1]+' </b>&nbsp;&nbsp;&nbsp;&nbsp;<br/> * '+infosPathologieMvcm[i][2]+' <span style="color: red; cursor: pointer; font-weight: bold;" onClick="ouvrirPopupResultat('+infosPathologieMvcm[i][3]+')"> &#128065; </span>'+  
                                    									'</div>'+
                                				  					  '</div>';
                                    				
                                    				scriptTextMap +=  '<script>'+
                                										'hideables.push("fleche_'+infosPathologieMvcm[i][0]+'");'+
                                										'hideables.push("box_'+infosPathologieMvcm[i][0]+'");'+
                                									  '</script>';	
                                				}
    				

				             
    	}
    	
    	scriptImageMap += '</map>';
    	
    	
    	scriptTextMap += '<script>'+
							'function hide_all() {'+
							  'for(i = 0; i < hideables.length; i++) {'+
							    'document.getElementById(hideables[i]).style.display="none";'+
							  '}'+
							'}'+
					
							'function ouvrirPopupResultat(idDemande) {'+
								'hide_all(); resultatAnalyse(idDemande); '+
							'}'+
					     '</script>';
    	
    	scriptImageMap += scriptTextMap;
    	
    	return scriptImageMap;
    }
    
    
    
    
  
    /**
     * TIMELINE DIAGRAMME --- TIMELINE DIAGRAMME --- TIMELINE DIAGRAMME
     * TIMELINE DIAGRAMME --- TIMELINE DIAGRAMME --- TIMELINE DIAGRAMME
     */
    var ouverturePopUpTimeLine = 0; 
    function getTimeLineDigramme(){
    	$( "#affichageFriseChronologique" ).dialog({
    		resizable: false,
    		height:580,
    		width:1100,
    		autoOpen: false,
    		modal: true,
    		buttons: {
    			"Terminer": function() {
    				$(this).dialog( "close" );
    			}
    		}
    	});
    	$("#affichageFriseChronologique").dialog('open');
    	
    	if(ouverturePopUpTimeLine == 0){ 
    		ouverturePopUpTimeLine = 1; 
    		getScriptTimeline();
    		
    		setTimeout(function(){
                $('.ui-widget-content').css({'text-align':'left'});
            });
    	}
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function listeDemandesAnalyses(){
        var oTable2 = $('#listeDemandesFiltre').dataTable
        ( {
        	"bDestroy":true,
    		"sPaginationType": "full_numbers",
    		"aLengthMenu": [3,5],
    		"iDisplayLength": 3,
        	"aaSorting": [],
        	"oLanguage": {
        		"sInfo": "_START_ &agrave; _END_ sur _TOTAL_ ",
        		"sInfoEmpty": "0 &eacute;l&eacute;ment &agrave; afficher",
        		"sInfoFiltered": "",
        		"sUrl": "",
        		"oPaginate": {
        			"sFirst":    "|<",
        			"sPrevious": "<",
        			"sNext":     ">",
        			"sLast":     ">|",
        		},
        		
        	},

        } );
        
        var asInitVals = new Array();
    	
    	//le filtre du select
    	$('#filter_statut').change(function() 
    	{					
    		oTable2.fnFilter( this.value );
    	});
    	
    	$(".foot_style_demande input").keyup( function () {
    		/* Filter on the column (the index) of this element */
    		oTable2.fnFilter( this.value, $(".foot_style_demande input").index(this) );
    	} );
    	
    	/*
    	 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
    	 * the footer
    	 */
    	$(".foot_style_demande input").each( function (i) {
    		asInitVals[i] = this.value;
    	} );
    	
    	$(".foot_style_demande input").focus( function () {
    		if ( this.className == "search_init" )
    		{
    			this.className = "";
    			this.value = "";
    		}
    	} );
    	
    	$(".foot_style_demande input").blur( function (i) {
    		if ( this.value == "" )
    		{
    			this.className = "search_init";
    			this.value = asInitVals[$(".foot_style_demande input").index(this)];
    		}
    	} );
    	
    }
    
    

    function listeAnalysesDemandes()
    {
        var oTable2 = $('#listeAnalyseFiltre').dataTable
        ( {
        	"bDestroy":true,
    		"sPaginationType": "full_numbers",
    		"aLengthMenu": [5,10],
    		"iDisplayLength": 5,
        	"aaSorting": [],
        	"oLanguage": {
        		"sInfo": "_START_ &agrave; _END_ sur _TOTAL_ analyses",
        		"sInfoEmpty": "0 &eacute;l&eacute;ment &agrave; afficher",
        		"sInfoFiltered": "",
        		"sUrl": "",
        		"oPaginate": {
        			"sFirst":    "|<",
        			"sPrevious": "<",
        			"sNext":     ">",
        			"sLast":     ">|",
        		},
        		
        	},

        } );
        
        var asInitVals = new Array();
    	
    	//le filtre du select
    	$('#filter_statut').change(function() 
    	{					
    		oTable2.fnFilter( this.value );
    	});
    	
    	$(".foot_style_analyse input").keyup( function () {
    		/* Filter on the column (the index) of this element */
    		oTable2.fnFilter( this.value, $(".foot_style_analyse input").index(this) );
    	} );
    	
    	/*
    	 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
    	 * the footer
    	 */
    	$(".foot_style_analyse input").each( function (i) {
    		asInitVals[i] = this.value;
    	} );
    	
    	$(".foot_style_analyse input").focus( function () {
    		if ( this.className == "search_init" )
    		{
    			this.className = "";
    			this.value = "";
    		}
    	} );
    	
    	$(".foot_style_analyse input").blur( function (i) {
    		if ( this.value == "" )
    		{
    			this.className = "search_init";
    			this.value = asInitVals[$(".foot_style_analyse input").index(this)];
    		}
    	} );
    	
    }
    
    function vueListeAnalysesValidees(iddemande){
    	var chemin = tabUrl[0]+'public/biologiste/get-liste-analyses-demandees-validees';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data:'iddemande='+iddemande,
            success: function(data) {
            	     var result = jQuery.parseJSON(data);
            	     //Ici on modifie les icones 
            	     $('.iconeListeAffichee').html("<img style='padding-left: 3px; cursor: pointer;' src='../images_icons/transfert_droite2.png' />");
            	     $('.visualiser'+iddemande).html("<img style='padding-left: 3px; ' src='../images_icons/transfert_droite.png' />");
            	     $('.dateAffichee').css({'color' : 'black'});
            	     $('.dateAffichee_'+iddemande).css({'color' : 'green'});
            	     
            	     
            	     $('#liste_analyses_demandes').html(result);
            	     listeAnalysesDemandes();
            }
        });
    }
    
    
    
    //Impression des r�sultats des analyses demand�es
    //Impression des r�sultats des analyses demand�es
    //Impression des r�sultats des analyses demand�es
    function imprimerResultatsAnalysesDemandees(iddemande)
    {
    	if(iddemande){
    		var vart = tabUrl[0]+'public/biologiste/impression-resultats-analyses-demandees';
    		var FormulaireImprimerAnalysesDemandees = document.getElementById("FormulaireImprimerDemandesAnalyses");
    		FormulaireImprimerAnalysesDemandees.setAttribute("action", vart);
    		FormulaireImprimerAnalysesDemandees.setAttribute("method", "POST");
    		FormulaireImprimerAnalysesDemandees.setAttribute("target", "_blank");
    		
    		//Ajout dynamique de champs dans le formulaire
    		var champ = document.createElement("input");
    		champ.setAttribute("type", "hidden");
    		champ.setAttribute("name", 'iddemande');
    		champ.setAttribute("value", iddemande);
    		FormulaireImprimerAnalysesDemandees.appendChild(champ);
    		$("#ImprimerDemandesAnalyses").trigger('click');
    	}
    	
    }

    function popupFermer() 
    {
    	$(null).w2overlay(null);
    }
    
    function diagnostic(id)
    {
    	
    	$('#diagnostic_'+id).w2overlay({ html: "" +
    		"" +
    		"<div style='border-bottom:1px solid green; height: 30px; background: #f9f9f9; width: 300px; text-align:center; padding-top: 10px; font-size: 13px; color: green; font-weight: bold;'>Diagnostic</div>" +
    		"<div style='height: 195px; width: 300px; padding-top:10px; text-align:center;'>" +
    		"<textarea style='height: 90%; width: 95%; max-height: 90%; max-width: 95%;' id='diagnostic_demande' > </textarea>" +
    		"</div>" +
    		"<script> $('#diagnostic_demande').val($('#diagnostic_demande_text').val()).attr({'readonly': true}).css({'background':'#fefefe'}); </script>" 
    	});
    	
    }
    
    function infos_parentales(id)
    {
    	
    	$('#infos_parentales_'+id).w2overlay({ html: "" +
    		"" +
    		"<div style='border-bottom:1px solid green; height: 30px; background: #f9f9f9; width: 600px; text-align:center; padding-top: 10px; font-size: 13px; color: green; font-weight: bold;'><img style='padding-right: 10px;' src='"+tabUrl[0]+"public/images_icons/Infos_parentales.png' >Informations parentales</div>" +
    		"<div style='height: 245px; width: 600px; padding-top:10px; text-align:center;'>" +
    		"<div style='height: 77%; width: 95%; max-height: 77%; max-width: 95%; ' class='infos_parentales' align='left'>  </div>" +
    		"</div>"+
    		"<script> $('.infos_parentales').html( $('.infos_parentales_tampon').html() ); </script>" 
    	});
    	
    }
    
    //Validation des r�sultats des analyses
    //Validation des r�sultats des analyses
    //Validation des r�sultats des analyses
    function validerResultatAnalyse(id)
    {
    	$('#resultat_existe'+id).w2overlay({ html: "" +
    		"" +
  			"<div style='border-bottom:1px solid green; height: 30px; background: #f9f9f9; width: 180px; text-align:center; padding-top: 10px; font-size: 13px; color: green; font-weight: bold;'>Retirer la validation</div>" +
  			"<div style='height: 50px; width: 180px; padding-top:10px; text-align:center;'>" +
  			"<button class='btn' style='cursor:pointer;' onclick='popupFermer(); return false;'>Non</button>" +
  			"<button class='btn' style='cursor:pointer;' onclick='retraitValidationResultat("+id+"); return false;'>Oui</button>" +
  			"</div>" +
  			"" 
    	});
    	
    }
    
    function popupFermer() {
    	$(null).w2overlay(null);
    }
     
    function retraitValidationResultat(iddemande) {
    	$(null).w2overlay(null); 
    	
    	
        $.ajax({
            type: 'POST',
            url: tabUrl[0]+'public/biologiste/retirer-validation' ,
            data:'iddemande='+iddemande,
            success: function(data) {
            	     
            	var result = jQuery.parseJSON(data);
            	
            	$('.resultat_existe'+iddemande).html("");
            	
            	if(result == 0){
            	    $(location).attr("href",tabUrl[0]+'public/biologiste/liste-resultats-valides');
            	}
            	
            }
        });
    	
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    //PARTIE DE LA GESTION DES ANALYSES --- PARTIE DE LA GESTION DES ANALYSES
    
    function vueListeAnalyses(iddemande){ 
    	var chemin = tabUrl[0]+'public/biologiste/get-liste-analyses-demandees';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data:'iddemande='+iddemande,
            success: function(data) {
            	     var result = jQuery.parseJSON(data);
            	     //Ici on modifie les icones 
            	     $('.iconeListeAffichee').html("<img style='padding-left: 3px; cursor: pointer;' src='../images_icons/transfert_droite2.png' />");
            	     $('.visualiser'+iddemande).html("<img style='padding-left: 3px; ' src='../images_icons/transfert_droite.png' />");
            	     $('.dateAffichee').css({'color' : 'black'});
            	     $('.dateAffichee_'+iddemande).css({'color' : 'green'});
            	     
            	     
            	     $('#liste_analyses_demandes').html(result);
            	     listeAnalysesDemandes();
            }
        });
    }
    
    function gestionFormuleLeucocytaire(){
    	
    	//Polynucléaires neutrophiles
    	//Polynucléaires neutrophiles
    	$("#champ1, #champ7").keyup( function () {
    		var champ1 = $("#champ1").val();
    		var champ7 = $("#champ7").val();
    		if( champ1 && champ7 ){
    			var resultatChamp2 = (champ1*champ7)/100;
    			$("#champ2").val(resultatChamp2);
    		}
    		else { $("#champ2").val(null); }
    		
    	} ).change( function () {
    		var champ1 = $("#champ1").val();
    		var champ7 = $("#champ7").val();
    		if( champ1 && champ7 ){
    			var resultatChamp2 = (champ1*champ7)/100;
    			$("#champ2").val(resultatChamp2);
    		}
    		else { $("#champ2").val(null); }
    	} );
    	
    	//Polynucléaires eosinophiles
    	//Polynucléaires eosinophiles
    	$("#champ1, #champ8").keyup( function () {
    		var champ1 = $("#champ1").val();
    		var champ8 = $("#champ8").val();
    		if( champ1 && champ8 ){
    			var resultatChamp3 = (champ1*champ8)/100;
    			$("#champ3").val(resultatChamp3);
    		}
    		else { $("#champ3").val(null); }
    		
    	} ).change( function () {
    		var champ1 = $("#champ1").val();
    		var champ8 = $("#champ8").val();
    		if( champ1 && champ8 ){
    			var resultatChamp3 = (champ1*champ8)/100;
    			$("#champ3").val(resultatChamp3);
    		}
    		else { $("#champ3").val(null); }
    	} );
    	
    	//Polynucléaires basophiles
    	//Polynucléaires basophiles
    	$("#champ1, #champ9").keyup( function () {
    		var champ1 = $("#champ1").val();
    		var champ9 = $("#champ9").val();
    		if( champ1 && champ9 ){
    			var resultatChamp4 = (champ1*champ9)/100;
    			$("#champ4").val(resultatChamp4);
    		}
    		else { $("#champ4").val(null); }
    		
    	} ).change( function () {
    		var champ1 = $("#champ1").val();
    		var champ9 = $("#champ9").val();
    		if( champ1 && champ9 ){
    			var resultatChamp4 = (champ1*champ9)/100;
    			$("#champ4").val(resultatChamp4);
    		}
    		else { $("#champ4").val(null); }
    		
    	} );
    	
    	//Lymphocytes Lymphocytes
    	//Lymphocytes Lymphocytes
    	$("#champ1, #champ10").keyup( function () {
    		var champ1 = $("#champ1").val();
    		var champ10 = $("#champ10").val();
    		if( champ1 && champ10 ){
    			var resultatChamp5 = (champ1*champ10)/100;
    			$("#champ5").val(resultatChamp5);
    		}
    		else { $("#champ5").val(null); }
    		
    	} ).change( function () {
    		var champ1 = $("#champ1").val();
    		var champ10 = $("#champ10").val();
    		if( champ1 && champ10 ){
    			var resultatChamp5 = (champ1*champ10)/100;
    			$("#champ5").val(resultatChamp5);
    		}
    		else { $("#champ5").val(null); }
    	} );
    	
    	//Monocytes Monocytes
    	//Monocytes Monocytes
    	$("#champ1, #champ11").keyup( function () {
    		var champ1 = $("#champ1").val();
    		var champ11 = $("#champ11").val();
    		if( champ1 && champ11 ){
    			var resultatChamp6 = (champ1*champ11)/100;
    			$("#champ6").val(resultatChamp6);
    		}
    		else { $("#champ6").val(null); }
    		
    	} ).change( function () {
    		var champ1 = $("#champ1").val();
    		var champ11 = $("#champ11").val();
    		if( champ1 && champ11 ){
    			var resultatChamp6 = (champ1*champ11)/100;
    			$("#champ6").val(resultatChamp6);
    		}
    		else { $("#champ6").val(null); }
    	} );
    	
    	
    	//Taux de r�ticulocytes -- Taux de r�ticulocytes
    	//Taux de r�ticulocytes -- Taux de r�ticulocytes
    	$("#champ12, #champ25").keyup( function () {
    		var champ12 = $("#champ12").val();
    		var champ25 = $("#champ25").val();
    		if( champ12 && champ25 ){
    			var resultatChamp24 = champ12*10000*champ25;
    			$("#champ24").val(resultatChamp24);
    		}
    		else { $("#champ24").val(null); }
    		
    	} ).change( function () {
    		var champ12 = $("#champ12").val();
    		var champ25 = $("#champ25").val();
    		if( champ12 && champ25 ){
    			var resultatChamp24 = champ12*10000*champ25;
    			$("#champ24").val(resultatChamp24);
    		}
    		else { $("#champ24").val(null); }
    	} );
    }
    
    function getChampsNfs(){
    	var tab = new Array();
    	var i;
    	for(i = 1 ; i <= 25 ; i++){
    		if($('#champ'+i).val()){ tab[i] = $('#champ'+i).val(); }
    		else { tab[i] = null; }
    	}
    	tab[i] = $('#type_materiel_nfs').val(); 
    	tab[i+1] = $('#commentaire_hemogramme').val();
    	
    	return tab;
    }
    
    
    function getTypageHemoglobine(){
    	var tab = [];
    	tab[1] = $('#type_materiel_typage_hemoglobine').val();
    	tab[2] = $('#typage_hemoglobine').val();
    	
    	return tab;
    }
    
    function getElectroHemo(){
    	$('#electro_hemo_moins').toggle(false);
	    
    	$('#electro_hemo_plus').click(function(){
	    	var nbLigne = $("#electro_hemo tr").length;
	    	$('#electro_hemo_moins').toggle(true);
	    	
	    	if(nbLigne < 10){
	    		var html ="<tr id='electro_hemo_"+nbLigne+"' class='ligneAnanlyse' style='width: 100%;'>"+
                            "<td style='width: 45%;'><label class='lab1'><span style='font-weight: bold; '>  <input id='electro_hemo_label_"+nbLigne+"' type='text' style='font-weight: bold; padding-right: 5px; margin-right: 30px;' readonly> </span></label></td>"+
                            "<td style='width: 35%;'><label class='lab2' style='padding-top: 5px;'> <input id='electro_hemo_valeur_"+nbLigne+"' type='number' step='any' readonly> % </label></td>"+
                            "<td style='width: 20%;'><label class='lab3' style='padding-top: 5px; width: 80%;'> </label></td>"+
                          "</tr>";

		    	$('#electro_hemo_'+(nbLigne-1)).after(html);
		    	
		    	if(nbLigne == 9){
		    		$('#electro_hemo_plus').toggle(false);
		    	}
	    	}

	    });
	    
	    $('#electro_hemo_moins').click(function(){ 
	    	var nbLigne = $("#electro_hemo tr").length;
	    	
	    	if(nbLigne > 2){
		    	$('#electro_hemo_'+(nbLigne-1)).remove();
		    	if(nbLigne == 3){ 
		    		$('#electro_hemo_moins').toggle(false);
		    	}
		    	
		    	if(nbLigne == 10){
		    		$('#electro_hemo_plus').toggle(true);
		    	}
	    	}

	    });
    }

    function getElectrophoreseHemoglobine(){
    	var tab = [];
    	var nbLigne = $("#electro_hemo tr").length;
    	var j = 1;
    	
    	tab[0] = $('#type_materiel_electro_hemo').val();
    	tab[1] = new Array(); 
    	tab[2] = new Array(); 
    	for(var i=1 ; i<nbLigne ; i++){
    		var label  = $('#electro_hemo_label_'+i ).val();
    		var valeur = $('#electro_hemo_valeur_'+i).val();
    		if(label && valeur){
        		tab[1][j]   = label;
        		tab[2][j++] = valeur;
    		}
    	}
	    
	    return tab;
    }
    
    function testCombsDirect(){
    	var tab = [];
    	tab[1] = $('#test_combs_direct').val(); 
		tab[2] = $('#titre_combs_direct').val();
    	
    	return tab;
    }
    
    function getTestCombsDirect(val){
    	if(val == 'Positif'){
    		$('.titre_combs_direct').toggle(true);
    	}else{
    		$('.titre_combs_direct').toggle(false).val(null);
    	}
    }
    
    function testCombsIndirect(){
    	var tab = [];
    	tab[1] = $('#test_combs_indirect').val(); 
		tab[2] = $('#titre_combs_indirect').val();
    	
    	return tab;
    }
    
    function getTestCombsIndirect(val){
    	if(val == 'Positif'){
    		$('.titre_combs_indirect').toggle(true);
    	}else{
    		$('.titre_combs_indirect').toggle(false).val(null);
    	}
    }
    
    function testCompatibilite(){
    	var tab = [];
    	tab[1] = $('#test_compatibilite').val(); 
		tab[2] = $('#titre_test_compatibilite').val();
    	
    	return tab;
    }
    
    function getTestCompatibilite(val){
    	if(val == 'Compatible'){
    		$('.titre_test_compatibilite').toggle(true);
    	}else{
    		$('.titre_test_compatibilite').toggle(false).val(null);
    	}
    }
    
    function getFerSeriqueFormule(){
    	var fer_serique_ug = $('#fer_serique_ug').val();
    	var valeur_mmol = null;
    	
    	$('#fer_serique_ug').keyup( function () {
    		fer_serique_ug = $('#fer_serique_ug').val();
    		if(fer_serique_ug){
        		valeur_mmol = fer_serique_ug * 0.1791;
        		$('#fer_serique_mmol').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#fer_serique_mmol').val(null);
        	}
    	}).change( function(){
    		fer_serique_ug = $('#fer_serique_ug').val();
    		if(fer_serique_ug){
        		valeur_mmol = fer_serique_ug * 0.1791;
        		$('#fer_serique_mmol').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#fer_serique_mmol').val(null);
        	}
    	});
    	
    }
    
    
 // GESTION DE L'ANALYSE ECBU 
    // GESTION DE L'ANALYSE ECBU 
    // GESTION DE L'ANALYSE ECBU 
    
    function getFloreCocciEcbuPositif(id){
    	if(id==3){ 
    		$(".flore_cocci_positif_ecbu").css({'visibility':'visible'});
    	}else{
    		$(".flore_cocci_positif_ecbu").css({'visibility':'hidden'});
    	}
    }
    
    
    function getListeCulotPositif(){
    	var tab = new Array();
    	var nbLigne = $("#culot_ecbu_tableau tr").length;
    	
    	for(var i=1 ; i<nbLigne ; i++){
    		var culotEcbuSelect = $('#culot_ecbu_select_'+i).val();
    		if(culotEcbuSelect != 4){
        		var culotEcbuValSel = $('#culot_ecbu_valsel_'+i+' select').val();
    		}else{
        		var culotEcbuValSel = $('#culot_ecbu_valsel_'+i+' input').val();
    		}
    		
    		tab[i] = culotEcbuSelect+'^$%$^'+culotEcbuValSel;
    	}
    	
    	return tab;
    }
    
    function getECBU(){
    	var tab = [];
    	tab[1] = $('#type_materiel_ecbu').val();
    	tab[2] = $('#urines_ecbu').val();
    	tab[3] = $('#leucocytes_ecbu').val();
    	tab[4] = $('#leucocytes_champ_ecbu').val();
    	tab[5] = $('#hematies_ecbu').val();
    	tab[6] = $('#hematies_champ_ecbu').val();
    	tab[7] = $('#levures_ecbu').val();
    	tab[8] = $('#filaments_myceliens_ecbu').val();
    	tab[9] = $('#flore_ecbu').val();
    	if($("#flore_cocci_pos_Choix1_ecbu").get(0).checked){ tab[10] = 1; }else{ tab[10] = -1; } 
    	if($("#flore_cocci_pos_Choix2_ecbu").get(0).checked){ tab[11] = 1; }else{ tab[11] = -1; } 
    	tab[12] = $('#culot_ecbu').val();
    	//Pour la liste des culots selectionn�s
    	if( tab[12] == 1 ){ tab[13] = getListeCulotPositif(); }
    	//-------------------------------------
    	tab[14] = $('#culture_ecbu').val();
    	if($("#culture_pos_Choix1_ecbu").get(0).checked){ tab[15] = 1; }else{ tab[15] = -1; }
    	if($("#culture_pos_Choix2_ecbu").get(0).checked){ tab[16] = 1; }else{ tab[16] = -1; }
    	tab[17] = $('#identification_culture_select_ecbu').val();
    	
    	tab[18] = $('#commentaire_ecbu').val();
    	
    	return tab;
    }
    
    
    
    
    
    
    
    
    var tabInfosCulotEcbu = new Array();
    tabInfosCulotEcbu[0] = "";
    
    tabInfosCulotEcbu[1] = '<select name="culot_ecbu_val_1" id="culot_ecbu_val_1" style="width: 95%;"> ' +
							   "  <option></option> " +
							   "  <option value=1>Schistoma haematobium</option> " +
							   "  <option value=2>Schistoma mansoni</option> " +
							   "</select>"; 
    
    tabInfosCulotEcbu[2] = '<select name="culot_ecbu_val_2" id="culot_ecbu_val_2" style="width: 95%;"> ' +
    		                   "  <option></option> " +
    		                   "  <option value=1>Oxalate de calcium</option> " +
    		                   "  <option value=2>Phosphate</option> " +
    		                   "  <option value=3>Acide Urique</option> " +
    		                   "  <option value=4>Phospho-ammoniacomagnesien</option> " +
    		                   "</select>"; 
    
    tabInfosCulotEcbu[3] = '<select name="culot_ecbu_val_3" id="culot_ecbu_val_3" style="width: 95%;"> ' +
                               "  <option></option> " +
                               "  <option value=1>Hematiques</option> " +
                               "  <option value=2>Leucocytaires</option> " +
                               "</select>"; 
    
    tabInfosCulotEcbu[4] = '<input type="text" name="culot_ecbu_val_4" id="culot_ecbu_val_4" style="width:95%; text-align: left; padding-left: 3px;">';
    
    function listeElemtsCulotEcbuSelect(indice, val){
    	$('#culot_ecbu_ligne_'+indice+' .emplaceListeElemtsCUSelect').html(tabInfosCulotEcbu[val]);
    }
    
    function ajoutCulotEcbuAuto(){
    	
    	$('#culot_ecbu_plus').click(function(){
	    	var nbLigne = $("#culot_ecbu_tableau tr").length;
	    	$('#culot_ecbu_moins').toggle(true);
	    	
	    	if(nbLigne < 20){
	    		var html ="<tr id='culot_ecbu_ligne_"+nbLigne+"' class='ligneAnanlyse' style='width: 100%;'>"+
	    		          "  <td style='width: 20%;'></td>"+
	    		          "  <td style='width: 20%;'>"+
	    		          "    <label class='lab1 listeSelect'>"+
	    		          "      <select onchange='listeElemtsCulotEcbuSelect("+nbLigne+",this.value);' name='culot_ecbu_select_"+nbLigne+"' id='culot_ecbu_select_"+nbLigne+"' >"+
			    		  "        <option value=0 > </option> "+
		                  "        <option value=1 >Oeufs</option> "+ 
		                  "        <option value=2 >Cristaux</option> "+ 
		                  "        <option value=3 >Cylindres</option> "+
		                  "        <option value=4 >Parasites</option>  "+
		                  "      </select>"+ 
		   	              "    </label>"+
		   	              "  </td>"+
		                         
	    		          "  <td style='width: 40%;'><label class='lab2 emplaceListeElemtsCUSelect' id='culot_ecbu_valsel_"+nbLigne+"' style='padding-top: 5px;'>  </label></td>"+
	    	              "  <td style='width: 20%;'></td>"+
                          "</tr>";

		    	$('#culot_ecbu_ligne_'+(nbLigne-1)).after(html);
		    	
		    	if(nbLigne == 19){
		    		$('#culot_ecbu_plus').toggle(false);
		    	}
	    	}

	    });
    	
    	$('#culot_ecbu_moins').click(function(){ 
	    	var nbLigne = $("#culot_ecbu_tableau tr").length;
	    	
	    	if(nbLigne > 2){
		    	$('#culot_ecbu_ligne_'+(nbLigne-1)).remove();
		    	if(nbLigne == 3){ 
		    		$('#culot_ecbu_moins').toggle(false);
		    	}
		    	
		    	if(nbLigne == 20){
		    		$('#culot_ecbu_plus').toggle(true);
		    	}
	    	}

	    });
    	
    }
    
    function getCulotEcbuListeSelect(){
    	var tab = [];
    	var nbLigne = $("#culot_ecbu_tableau tr").length;
    	var j = 1;
    	
    	tab[0] = $('#type_materiel_culot_ecbu').val();
    	tab[1] = new Array(); 
    	tab[2] = new Array(); 
    	for(var i=1 ; i<nbLigne ; i++){
    		var listeSelect1  = $('#culot_ecbu_ligne_'+i+' .listeSelect select' ).val();
    		var listeSelect2 = $('#culot_ecbu_ligne_'+i+' .emplaceListeElemtsCUSelect select').val();
    		tab[1][j]   = listeSelect1;
    		if(listeSelect2){ 
    			tab[2][j++] = listeSelect2;
    		}else{
    			tab[2][j++] = $('#culot_ecbu_ligne_'+i+' .emplaceListeElemtsCUSelect input').val();;
    		}
    	}
	    tab[3] = $('#conclusion_culot_ecbu_valeur').val();
	    
	    return tab;
    }
    
    function getCulotEcbuPositif(id){
    	if( id == 1 ){ 
    		$("#culot_ecbu_tableau").toggle(true);
    	}else{
    		$("#culot_ecbu_tableau").toggle(false);
    	}
    }
    
    var commentCultPosValECBU = "";
    var tabConcluCommentECBU = new Array();
        tabConcluCommentECBU[1] = "Absence d'infection du tractus urinaire";
        tabConcluCommentECBU[2] = "ECBU à Refaire";
    function getCultureEcbuPositif(id){
    	if(id==2){ 
    		$(".culture_ecbu_negatif").css({'visibility': 'visible'}); 
    		$("#culture_ecbu_choix12_positif").toggle(false);
    		$("#commentaire_ecbu").attr('readonly', true).val(tabConcluCommentECBU[1]);
    		$(".identificationCultureChampsECBU").toggle(false);
    	}
    	else if(id==1){ 
    		$("#culture_ecbu_choix12_positif").toggle(true);  
    		$(".culture_ecbu_negatif").css({'visibility': 'hidden'});  
    		$("#culture_pos_Choix1_ecbu, #culture_pos_Choix2_ecbu").prop("checked", false);
        	if(commentCultPosValECBU === tabConcluCommentECBU[1] || commentCultPosValECBU === tabConcluCommentECBU[2]){ commentCultPosValECBU = ""; }
    		$("#commentaire_ecbu").attr('readonly', true).val(commentCultPosValECBU);
    	}else{
    		$(".culture_ecbu_negatif").css({'visibility': 'hidden'}); 
    		$("#culture_ecbu_choix12_positif").toggle(false);
    		$("#commentaire_ecbu").attr('readonly', true).val(commentCultPosValECBU);
    		$(".identificationCultureChampsECBU").toggle(false);
    	}
    	
    }
    
    function getCultPosChoix1Ecbu(){
    	
    	$("#culture_pos_Choix1_ecbu").prop("checked", true);
    	$("#culture_pos_Choix2_ecbu").prop("checked", false);
    	
    	$(".identificationCultureChampsECBU").toggle(true); 
    	if(commentCultPosValECBU === tabConcluCommentECBU[1] || commentCultPosValECBU === tabConcluCommentECBU[2]){ commentCultPosValECBU = ""; }
    	$("#commentaire_ecbu").attr('readonly', true).val(commentCultPosValECBU);
    }
    
    function getCultPosChoix2Ecbu(){
    	
    	$("#culture_pos_Choix1_ecbu").prop("checked", false);
    	$("#culture_pos_Choix2_ecbu").prop("checked", true);
    	
    	$("#commentaire_ecbu").attr('readonly', true).val(tabConcluCommentECBU[2]); 
    	$(".identificationCultureChampsECBU").toggle(false);
    }

    
    function getLevuresFilMycEcbuPositif(id){
    	if(id==1){
    		$('.filamMycECBUToggle').css({'visibility':'visible'});
    	}else{
    		$('.filamMycECBUToggle').css({'visibility':'hidden'});
    	}
    }
    
    
    function getListeDesSouchesIdentificationCultureECBU(){
    	
    	
    	$.ajax({
              type: 'POST',
              url: tabUrl[0]+'public/biologiste/get-liste-des-souches',
              data:{},
              success: function(data) {
              	     var result = jQuery.parseJSON(data);
              	     
              	     $("#listeSouchesECBUSelect").html(result);
              }
          });
    }
    
    
    var resultParasitesEcbu = "";
    function getListeDesParasitesDansBdECBU(){
    	
    	$.ajax({
              type: 'POST',
              url: tabUrl[0]+'public/technicien/get-liste-des-parasites',
              data:{},
              success: function(data) {
              	     var result = jQuery.parseJSON(data);
              	     
              	     $("#listeParasitesECBUSelect").html(result);
              	     $("#parasites_ecbu").val(resultParasitesEcbu); 
              }
          });
    }
    
    
    function getIconeAntibiogrammeCulturePosECBU(id,i){
    	if(id == 0){
    		$('.antiBGButAffInterfaceCultECBU'+i).toggle(false);
    	}else{
    		$('.antiBGButAffInterfaceCultECBU'+i).toggle(true);
    	}
    }

    function antibiogrammeAfficherInterfaceECBU(){
    	
    	$( "#resultatsAnalysesECBUAntiBioGramme" ).dialog({
    		resizable: false,
    		height:680,
    		width:800,
    		autoOpen: false,
    		modal: true,
    		buttons: { 			

    			"Terminer": function() {
    				$(this).dialog( "close" );
    			}
    		}
    	});
    	
    	var cultureSelECBU = $('#identification_culture_select_ecbu').val();
    	var cultureSelLibECBU = $('#identification_culture_select_ecbu .SouchesCultIdentifID_'+cultureSelECBU).text();
    	$("#valeurSoucheIsoleeIdentificationCultureECBU").html(cultureSelLibECBU);
    	
    	$("#resultatsAnalysesECBUAntiBioGramme").dialog('open');
    }
    
    // FIN DE GESTION DE L'ANALYSE ECBU 
    // FIN DE GESTION DE L'ANALYSE ECBU 
    // FIN DE GESTION DE L'ANALYSE ECBU 
    
    //Resultats d'une seule analyse
    //Resultats d'une seule analyse
    //Resultats d'une seule analyse
    function resultatsAnalyses(idanalyse, iddemande){
    	var tab = [];
    	$( "#resultatsAnalyses" ).dialog({
    		resizable: false,
    		height:670,
    		width:750,
    		autoOpen: false,
    		modal: true,
    		buttons: {
//    			"Enregistrer": function() {
//    				     if(idanalyse ==  1) { tab    = getChampsNfs(); }
//    				else if(idanalyse ==  2) { tab[1] = $('#groupe').val(); tab[2] =  $('#rhesus').val(); }
//    				else if(idanalyse ==  3) { tab[1] = $('#antigene_d_faible').val(); }
//    				else if(idanalyse ==  4) { tab    = testCombsDirect(); }
//    				else if(idanalyse ==  5) { tab    = testCombsIndirect(); }
//       				else if(idanalyse ==  6) { tab    = testCompatibilite(); }
//       				else if(idanalyse ==  7) { tab[1] = $('#vitesse_sedimentation').val(); }
//    				else if(idanalyse ==  8) { tab[1] = $('#test_demmel').val(); }
//    				else if(idanalyse ==  9) { tab[1] = $('#taux_reticulocyte').val(); } 
//    				else if(idanalyse == 10) { tab[1] = $('#goutte_epaisse').val(); tab[2] = $('#densite_parasitaire').val(); }
//    				     
//    				else if(idanalyse == 14) { tab[1] = $('#temps_quick_temoin').val(); tab[2] = $('#temps_quick_patient').val(); tab[3] = $('#taux_prothrombine_patient').val(); tab[4] = $('#inr_patient').val(); }
//    				else if(idanalyse == 15) { tab[1] = $('#tca_patient').val(); tab[2] = $('#temoin_patient').val(); }
//    				else if(idanalyse == 16) { tab[1] = $('#fibrinemie').val();  }
//    				else if(idanalyse == 17) { tab[1] = $('#temps_saignement').val();  }
//    				     
//    				else if(idanalyse == 21) { tab[1] = $('#glycemie_1').val(); tab[2] = $('#glycemie_2').val(); }
//    				else if(idanalyse == 22) { tab[1] = $('#creatininemie').val(); }
//    				else if(idanalyse == 23) { tab[1] = $('#uree_sanguine').val(); }
//    				else if(idanalyse == 24) { tab[1] = $('#acide_urique').val(); }
//    				else if(idanalyse == 25) { tab[1] = $('#cholesterol_total_1').val(); tab[2] = $('#cholesterol_total_2').val(); }
//    				else if(idanalyse == 26) { tab[1] = $('#triglycerides_1').val(); tab[2] = $('#triglycerides_2').val(); }
//    				else if(idanalyse == 27) { tab[1] = $('#cholesterol_HDL_1').val(); tab[2] = $('#cholesterol_HDL_2').val(); }
//    				else if(idanalyse == 28) { tab[1] = $('#cholesterol_LDL_1').val(); tab[2] = $('#cholesterol_LDL_2').val(); }
//    				else if(idanalyse == 29) { 
//    					tab[1] = $('#cholesterol_total_1').val(); tab[2] = $('#cholesterol_total_2').val();
//    				    tab[3] = $('#cholesterol_HDL_1').val();   tab[4] = $('#cholesterol_HDL_2').val(); 
//    				    tab[5] = $('#cholesterol_LDL_1').val();   tab[6] = $('#cholesterol_LDL_2').val(); 
//    				    tab[7] = $('#triglycerides_1').val();     tab[8] = $('#triglycerides_2').val(); 
//    				}
//    				else if(idanalyse == 30) { tab    = getLipidesTotaux(); }   
//    				else if(idanalyse == 31) { tab    = getIonogramme(); }     
//    				else if(idanalyse == 32) { tab[1] = $('#calcemie').val(); }
//    				else if(idanalyse == 33) { tab[1] = $('#magnesemie').val(); }
//    				else if(idanalyse == 34) { tab[1] = $('#phosphoremie').val(); }     
//    				else if(idanalyse == 35) { tab    = getAsat(); } 
//    				else if(idanalyse == 36) { tab    = getAlat(); }  
//    				else if(idanalyse == 37) { tab    = getAsatAlat(); }
//    				else if(idanalyse == 38) { tab[1] = $('#phosphatage_alcaline').val(); }   
//    				else if(idanalyse == 39) { tab[1] = $('#gama_gt').val(); }   
//    				else if(idanalyse == 40) { tab = getFerSerique(); }   
//    				else if(idanalyse == 41) { tab = getFerritinine(); }   
//    				else if(idanalyse == 42) { tab = getBilirubineTotaleDirecte(); }
//    				else if(idanalyse == 43) { tab = getHemoglobineGlyqueeHBAC(); } 
//    				else if(idanalyse == 44) { tab = getElectrophoreseHemoglobine(); }
//    				else if(idanalyse == 45) { tab = getElectrophoreseProteines(); }
//    				else if(idanalyse == 46) { tab = getAlbuminemie(); } 
//    				else if(idanalyse == 47) { tab = getAlbumineUrinaire(); } 
//    				else if(idanalyse == 48) { tab = getProtidemie(); } 
//    				else if(idanalyse == 49) { tab = getProteinurie(); } 
//    				else if(idanalyse == 50) { tab = getHlmCompteDaddis(); } 
//    				else if(idanalyse == 51) { tab = getBetaHcgPlasmatique(); } 
//    				else if(idanalyse == 52) { tab = getPsa(); } 
//    				else if(idanalyse == 53) { tab = getCrp(); } 
//    				else if(idanalyse == 54) { tab = getFacteursRhumatoides(); } 
//    				else if(idanalyse == 55) { tab = getRfWaalerRose(); } 
//    				else if(idanalyse == 56) { tab = getToxoplasmose(); } 
//    				else if(idanalyse == 57) { tab = getRubeole(); } 
//    				else if(idanalyse == 58) { tab = getCulotUrinaire(); } 
//    				else if(idanalyse == 59) { tab = getSerologieChlamydiae(); }
//    				else if(idanalyse == 60) { tab = getSerologieSyphilitique(); } 
//    				else if(idanalyse == 61) { tab = getAslo(); } 
//    				else if(idanalyse == 62) { tab = getWidal(); } 
//    				else if(idanalyse == 63) { tab = getAgHbs(); } 
//    				     
//    				     
//    				     
//    				else if(idanalyse == 68) { tab = getTypageHemoglobine(); }
//    				     
//    				     
//    				     //alert(tab); return false;
//    				     
//    				$( this ).dialog( "close" );
//
//    				$.ajax({
//    					type: 'POST',
//    					url: tabUrl[0]+'public/biologiste/enregistrer-resultat',
//    					data: {'idanalyse':idanalyse, 'iddemande':iddemande, 'tab':tab},
//    					success: function(data) {
//    						var result = jQuery.parseJSON(data);  
//    						var resultatExiste = result[1];
//    						
//    						
//    						
//    						
//    						//-----------------------------------------
////    						if(resultatExiste == 0){
////    							$('.resultat_existe'+result[0]).empty();
////    						}else {
////    							$('.resultat_existe'+result[0]).html("<img  src='../images_icons/tick_16.png' />");
////    						}
//    						
//    						
//    						
//    						
//    					}
//    				});
//    			},
    			"Terminer": function() {
    				$(this).dialog( "close" );
    			}
    		}
    	});
    }
    
    function resultatAnalyse(iddemande){
    	var chemin = tabUrl[0]+'public/biologiste/recuperer-analyse';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data:'iddemande='+iddemande,
            success: function(data) {
            	     var result = jQuery.parseJSON(data); 
            	     var idanalyse = result[0];
            	     resultatsAnalyses(idanalyse, iddemande);
            	     
            	     $('#contenuResultatsAnalysesParType div').empty();
            	     $('#contenuResultatsAnalysesDuneDemande div').empty();
            	     $('#contenuResultatsAnalyses div').empty();
            	     $('#contenuResultatsAnalyses div').html(result[1]);
            	     gestionFormuleLeucocytaire();
            	     rapportCHOL_HDL();
            	     getCreatininemie_umol();
            	     getTcaRatio();
            	     getCholesterolHDL();
            	     getCholesterolLDL();
            	     getCholesterolTotal();
            	     getTriglycerides();
            	     getGlycemieFormule();
            	     getElectrophoreseProteinesFormule();
            	     getTestCombsDirect();
            	     getTestCombsIndirect();
            	     getTestCompatibilite();
            	     getAsatAlatAuto();
            	     getFerSeriqueFormule();
            	     ajoutCulotUrinaireAuto(); 
            	     ajoutCulotEcbuAuto();
            	     
            	     $("#resultatsAnalyses").dialog('open');
            	     
                 	 $('#commentaire_hemogramme, #commentaire_pv, #conclusion_pv_ABG, #conclusion_ecbu_ABG, #autres_precisions_commentaire_pv, #autres_precisions_commentaire_ecbu').attr('readonly', true);
                 	 $('#idCommentaireChoiceCheckPV').toggle(false);
                 	 //Ajouter des lignes
                 	 //Ajouter des lignes
           	         getTestCombsIndirectAjout();
           	         getElectroHemo();
                 	
                 	 //Bloquer la saisie sur tous les champs de commentaires
                 	 //Bloquer la saisie sur tous les champs de commentaires
                 	 $('.commentaire_protect textarea').attr('readonly', true);

            }
        });
    	
    }
    
    
    function getLipidesTotaux(){
    	var tab = [];
    	tab[1] = $('#type_materiel_lipides_totaux').val();
    	tab[2] = $('#lipides_totaux').val();
    	
    	return tab;
    }
    
    function getChampsGroupeRhesus(){
    	var tab = [];
    	tab[1] = $('#groupe').val();
    	tab[2] = $('#rhesus').val();
    	
    	return tab;
    }
    
    function getGoutteEpaisse(){
    	var tab = [];
    	tab[1] = $('#goutte_epaisse').val();
    	tab[2] = $('#densite_parasitaire').val();
    	
    	return tab;
    }
    
    function getTpInr(){
    	var tab = [];
    	tab[1] = $('#temps_quick_temoin').val(); 
    	tab[2] = $('#temps_quick_patient').val(); 
    	tab[3] = $('#taux_prothrombine_patient').val(); 
    	tab[4] = $('#inr_patient').val();
    	
    	return tab;
    }
    
    function getTca(){
    	var tab = [];
    	tab[1] = $('#tca_patient').val(); 
    	tab[2] = $('#temoin_patient').val();
    	
    	return tab;
    }
    
    function getGlycemie(){
    	var tab = [];
    	tab[1] = $('#glycemie_1').val(); 
    	tab[2] = $('#glycemie_2').val();
    	
    	return tab;
    }
    
    function getCholesterolTotal(){
    	var tab = [];
    	tab[1] = $('#cholesterol_total_1').val(); 
    	tab[2] = $('#cholesterol_total_2').val();
    	
    	return tab;
    }
    
    function getTriglycerides(){
    	var tab = [];
    	tab[1] = $('#triglycerides_1').val(); 
    	tab[2] = $('#triglycerides_2').val();
    	
    	return tab;
    }
    
    function getCholesterolHDL(){
    	var tab = [];
    	tab[1] = $('#cholesterol_HDL_1').val(); 
    	tab[2] = $('#cholesterol_HDL_2').val();
    	
    	return tab;
    }
    
    function getCholesterolLDL(){
    	var tab = [];
    	tab[1] = $('#cholesterol_LDL_1').val(); 
    	tab[2] = $('#cholesterol_LDL_2').val();
    	
    	return tab;
    }
    
    function getIonogramme(){
    	var tab = [];
    	tab[1] = $('#sodium_sanguin').val(); 
		tab[2] = $('#potassium_sanguin').val();
		tab[3] = $('#chlore_sanguin').val();
    	
    	return tab;
    }
    
    function getChol_Total_HDL_LDL_Trigly(){
    	var tab = [];
    	tab[1] = $('#cholesterol_total_1').val(); tab[2] = $('#cholesterol_total_2').val();
	    tab[3] = $('#cholesterol_HDL_1').val();   tab[4] = $('#cholesterol_HDL_2').val(); 
	    tab[5] = $('#cholesterol_LDL_1').val();   tab[6] = $('#cholesterol_LDL_2').val(); 
	    tab[7] = $('#triglycerides_1').val();     tab[8] = $('#triglycerides_2').val(); 
	    
	    return tab;
    }
    
    function getAsatAlat(){
    	var tab = [];
    	tab[1] = $('#type_materiel_tgp_alat').val();
    	tab[2] = $('#tgp_alat').val();
	    tab[3] = $('#type_materiel_tgo_asat').val();
	    tab[4] = $('#tgo_asat').val();
	    
	    return tab;
    }
    
    function getAlat(){
    	var tab = [];
    	tab[1] = $('#type_materiel_tgp_alat').val();
    	tab[2] = $('#tgp_alat').val();
	    
	    return tab;
    }
    
    function getAsat(){
    	var tab = [];
	    tab[1] = $('#type_materiel_tgo_asat').val();
	    tab[2] = $('#tgo_asat').val();
	    
	    return tab;
    }
    
    function getFerritinine(){
    	var tab = [];
    	tab[1] = $('#type_materiel_ferritinine').val();
    	tab[2] = $('#ferritinine').val();
	    
	    return tab;
    }
    
    function getBilirubineTotaleDirecte(){
    	var tab = [];
    	tab[1] = $('#type_materiel_bilirubine_totale_directe').val();
    	tab[2] = $('#bilirubine_totale_mg').val();
    	tab[3] = $('#bilirubine_totale_umol').val();
    	tab[4] = $('#bilirubine_directe').val();
	    
	    return tab;
    }
    
    function getHemoglobineGlyqueeHBAC(){
    	var tab = [];
    	tab[1] = $('#type_materiel_hemo_gly_hbac').val();
    	tab[2] = $('#hemoglobine_glyquee_hbac').val();
    	tab[3] = $('#hemoglobine_glyquee_hbac_mmol').val();
	    
	    return tab;
    }
    
    function getFerSerique(){
    	var tab = [];
    	tab[1] = $('#fer_serique_ug').val();
    	tab[2] = $('#fer_serique_umol').val();
    	
    	return tab;
    }
    
    function getElectrophoreseProteines(){
    	var tab = [];
    	
    	tab[1]  = $('#type_materiel_electro_proteine').val();
    	tab[2]  = $('#albumine').val();
    	tab[3]  = $('#albumine_abs').val();
    	tab[4]  = $('#alpha_1').val();
    	tab[5]  = $('#alpha_1_abs').val();
    	tab[6]  = $('#alpha_2').val();
    	tab[7]  = $('#alpha_2_abs').val();
    	tab[8]  = $('#beta_1').val();
    	tab[9]  = $('#beta_1_abs').val();
    	tab[10]  = $('#beta_2').val();
    	tab[11] = $('#beta_2_abs').val();
    	tab[12] = $('#gamma').val();
    	tab[13] = $('#gamma_abs').val();
    	tab[14] = $('#proteine_totale').val();
    	tab[15] = $('#commentaire_electrophorese_proteine').val();
    	
    	return tab;
    }
    
    function getAlbuminemie(){
    	var tab = [];
    	tab[1] = $('#type_materiel_albuminemie').val();
    	tab[2] = $('#albuminemie').val();
	    
	    return tab;
    }
    
    function getAlbumineUrinaire(){
    	var tab = [];
    	tab[1] = $('#type_materiel_albumine_urinaire').val();
    	
    	var albumine_urinaire = $('#albumine_urinaire').val();
    	tab[2] = albumine_urinaire;
    	tab[3] = null;
    	if(albumine_urinaire == 'positif'){ tab[3] = $('#albumine_urinaire_degres').val(); }
    	
    	var sucre_urinaire = $('#sucre_urinaire').val();
    	tab[4] = sucre_urinaire;
    	tab[5] = null;
    	if(sucre_urinaire == 'positif'){ tab[5] = $('#sucre_urinaire_degres').val(); }
    	
    	var corps_cetonique_urinaire = $('#corps_cetonique_urinaire').val();
    	tab[6] = corps_cetonique_urinaire;
    	tab[7] = null;
    	if(corps_cetonique_urinaire == 'positif'){ tab[7] = $('#corps_cetonique_urinaire_degres').val(); }
	    
	    return tab;
    }
    
    function getProtidemie(){
    	var tab = [];
    	tab[1] = $('#type_materiel_protidemie').val();
    	tab[2] = $('#protidemie').val();
	    
	    return tab;
    }
    
    function getProteinurie(){
    	var tab = [];
    	tab[1] = $('#type_materiel_proteinurie').val();
    	tab[2] = $('#proteinurie').val();
	    
	    return tab;
    }
    
    function getHlmCompteDaddis(){
    	var tab = [];
    	tab[1] = $('#type_materiel_hlm_compte_daddis').val();
    	tab[2] = $('#hematies_hlm').val();
    	tab[3] = $('#leucocytes_hlm').val();
	    
	    return tab;
    }
    
    function getBetaHcgPlasmatique(){
    	var tab = [];
    	tab[1] = $('#type_materiel_beta_hcg').val();
    	tab[2] = $('#beta_hcg_plasmatique').val();
	    
	    return tab;
    }
    
    function getPsa(){
    	var tab = [];
    	tab[1] = $('#type_materiel_psa').val();
    	tab[2] = $('#psa').val();
	    
	    return tab;
    }
    
    function getCrp(){
    	var tab = [];
    	tab[1] = $('#type_materiel_crp').val();
    	tab[2] = $('#optionResultatCrp').val();
    	tab[3] = $('#crpValeurResultat').val();
	    
	    return tab;
    }
    
    function getChoixResultatCrp(id){
    	if(id == 'positif'){
    		$('#crpValeurResultatChamp').css({'visibility':'visible'});
    	}else{
    		$('#crpValeurResultatChamp').css({'visibility':'hidden'});
    		$('#crpValeurResultat').val('')
    	}
    }
    
    function getFacteursRhumatoides(){
    	var tab = [];
    	tab[1] = $('#type_materiel_facteurs_rhumatoides').val();
    	tab[2] = $('#facteurs_rhumatoides').val();
	    
	    return tab;
    }
    
    function getRfWaalerRose(){
    	var tab = [];
    	tab[1] = $('#type_materiel_rf_waaler_rose').val();
    	tab[2] = $('#rf_waaler_rose').val();
	    
	    return tab;
    }
    
    function getToxoplasmose(){
    	var tab = [];
    	tab[1] = $('#type_materiel_toxoplasmose').val();
    	tab[2] = $('#toxoplasmose_1').val();
    	tab[3] = $('#toxoplasmose_2').val();
	    
	    return tab;
    }
    
    function getRubeole(){
    	var tab = [];
    	tab[1] = $('#type_materiel_rubeole').val();
    	tab[2] = $('#rubeole_1').val();
    	tab[3] = $('#rubeole_2').val();
	    
	    return tab;
    }
    
    
    
    
    function commentaireChoiceCheckPV(){
    	
    	$('#idCommentaireChoiceCheckPV').w2overlay({ html: "" +
    		"" +
    		"<div id='commentChoiceCheckPVID' style='height: 110px; width: 300px; padding-top:10px; text-align:left;'>" +
    		"<table>"+
    		"<tr><td class='col1'><button class='commentChoicePVBut But1' onclick='getCommentChoiceCheckPV(1)'></button></td> <td class='col2'><div class='commentChoicePVDiv' > Flore vaginale normale </div></td></tr>"+
    		"<tr><td class='col1'><button class='commentChoicePVBut But2' onclick='getCommentChoiceCheckPV(2)'></button></td> <td class='col2'><div class='commentChoicePVDiv' > Flore vaginale déséquilibrée </div></td></tr>"+
    		"<tr><td class='col1'><button class='commentChoicePVBut But3' onclick='getCommentChoiceCheckPV(3)'></button></td> <td class='col2'><div class='commentChoicePVDiv' > Vaginite à Trichomonas vaginalis </div></td></tr>"+
    		"<tr><td class='col1'><button class='commentChoicePVBut But4' onclick='getCommentChoiceCheckPV(4)'></button></td> <td class='col2'><div class='commentChoicePVDiv' > Vaginose bactérienne à Gardenella vaginalis </div></td></tr>"+
    		"<tr><td class='col1'><button class='commentChoicePVBut But5' onclick='getCommentChoiceCheckPV(5)'></button></td> <td class='col2'><div class='commentChoicePVDiv' > Candidose vaginale </div></td></tr>"+
    		
    		"</table>"+
    		"</div>"+
    		"<style> .w2ui-overlay:before{float: left; border: 1px solid green; top: 2px; border-bottom: 0px solid transparent; border-left:1px solid transparent;}  .w2ui-overlay{float: left; margin-left: -20px; border: 1px solid green; margin-top: 1 } </style>"
    		
    	});
    	
    	setCommenataireAutoChoiceCheckPV();
    }
    
    var tabCommentaireChoicePV = new Array();
    
        tabCommentaireChoicePV[1] = "Flore vaginale normale \r\n";
        tabCommentaireChoicePV[2] = "Flore vaginale déséquilibrée \r\n";
       	tabCommentaireChoicePV[3] = "Vaginite à Trichomonas vaginalis \r\n";
   		tabCommentaireChoicePV[4] = "Vaginose bactérienne à Gardenella vaginalis \r\n";
		tabCommentaireChoicePV[5] = "Candidose vaginale \r\n";
    var tabCommentaireSelect = new Array();	
		
    var pvBut1 = 0, pvBut2 = 0, pvBut3 = 0, pvBut4 = 0, pvBut5 = 0;
    function getCommentChoiceCheckPV(id){
    	
    	if(id == 1){ 
    		if(pvBut1 == 0){ $("#commentChoiceCheckPVID .But1").html("X"); pvBut1 = 1; tabCommentaireSelect[1] = 1;}
    		else{ $("#commentChoiceCheckPVID .But1").html(""); pvBut1 = 0; tabCommentaireSelect[1] = -1;}
    	}
    	
    	if(id == 2){
    		if(pvBut2 == 0){ $("#commentChoiceCheckPVID .But2").html("X"); pvBut2 = 1; tabCommentaireSelect[2] = 1;}
    		else{  $("#commentChoiceCheckPVID .But2").html(""); pvBut2 = 0;  tabCommentaireSelect[2] = -1;}
    	}
    	
    	if(id == 3){
    		if(pvBut3 == 0){ $("#commentChoiceCheckPVID .But3").html("X"); pvBut3 = 1; tabCommentaireSelect[3] = 1;}
    		else{  $("#commentChoiceCheckPVID .But3").html(""); pvBut3 = 0;  tabCommentaireSelect[3] = -1;}
    	}
    	
    	if(id == 4){
    		if(pvBut4 == 0){ $("#commentChoiceCheckPVID .But4").html("X"); pvBut4 = 1; tabCommentaireSelect[4] = 1;}
    		else{  $("#commentChoiceCheckPVID .But4").html(""); pvBut4 = 0;  tabCommentaireSelect[4] = -1;}
    	}
    	
    	if(id == 5){
    		if(pvBut5 == 0){ $("#commentChoiceCheckPVID .But5").html("X"); pvBut5 = 1; tabCommentaireSelect[5] = 1;}
    		else{  $("#commentChoiceCheckPVID .But5").html(""); pvBut5 = 0;  tabCommentaireSelect[5] = -1;}
    	}
    	
    	setCommenataireAutoChoiceCheckPV();
    }
    
    function setCommenataireAutoChoiceCheckPV(){
    	
    	var commentaire = "";
    	for(var i=1 ; i<=tabCommentaireSelect.length ; i++){
    		if(tabCommentaireSelect[i] && tabCommentaireSelect[i]!= -1){
        		commentaire += tabCommentaireChoicePV[i];
        		$("#commentChoiceCheckPVID .But"+i).html("X");
    		}
    	}
    	
    	$('#commentaire_pv').val(commentaire);
    }
    
    
    
    
    
    // GESTION DE L'ANALYSE Culot_urinaire 
    // GESTION DE L'ANALYSE Culot_urinaire 
    // GESTION DE L'ANALYSE Culot_urinaire 
    
    var tabInfosCulotUrinaire = new Array();
    tabInfosCulotUrinaire[0] = "";
    tabInfosCulotUrinaire[1] = "";
    tabInfosCulotUrinaire[2] = "";
    tabInfosCulotUrinaire[3] = '<select disabled name="culot_urinaire_val_3" id="culot_urinaire_val_3" style="width: 95%;"> ' +
    		                   "  <option></option> " +
    		                   "  <option value=1>Oxalate de potassium | calcium</option> " +
    		                   "  <option value=2>Phosphate</option> " +
    		                   "  <option value=3>Cystine</option> " +
    		                   "  <option value=4>Acide Urique</option> " +
    		                   "</select>"; 
    
    tabInfosCulotUrinaire[4] = '<select disabled name="culot_urinaire_val_4" id="culot_urinaire_val_4" style="width: 95%;"> ' +
                               "  <option></option> " +
                               "  <option value=1>Schistoma hematobium</option> " +
                               "</select>"; 
    
    tabInfosCulotUrinaire[5] = '<select disabled name="culot_urinaire_val_5" id="culot_urinaire_val_5" style="width: 95%;"> ' +
                               "  <option></option> " +
                               "  <option value=1>Trichomonas vaginale</option> " +
                               "  <option value=2>Schistosoma hematobium</option> " +
                               "</select>"; 
    
    function listeElemtsCulotUrinaireSelect(indice, val){
    	$('#culot_urinaire_ligne_'+indice+' .emplaceListeElemtsCUSelect').html(tabInfosCulotUrinaire[val]);
    }
    
    function ajoutCulotUrinaireAuto(){
    	
    	$('#culot_urinaire_plus').click(function(){
	    	var nbLigne = $("#culot_urinaire_tableau tr").length;
	    	$('#culot_urinaire_moins').toggle(true);
	    	
	    	if(nbLigne < 10){
	    		var html ="<tr id='culot_urinaire_ligne_"+nbLigne+"' class='ligneAnanlyse' style='width: 100%;'>"+
	    		          "  <td style='width: 40%;'><label class='lab1 listeSelect'><span style='font-weight: bold; '> <select disabled onchange='listeElemtsCulotUrinaireSelect("+nbLigne+",this.value);' name='culot_urinaire_select' id='culot_urinaire_select' > <option value=0>  </option> <option value='1' >Leucocytes</option> <option value='2' >H&eacute;maties</option> <option value='3' >Cristaux</option> <option value='4' >Oeufs</option> <option value='5' >Parasites</option> </select> </span></label></td>"+
	    	              "  <td style='width: 40%;'><label class='lab2 emplaceListeElemtsCUSelect' style='padding-top: 5px;'>  </label></td>"+
	    	              "  <td style='width: 20%;'><label class='lab3' style='padding-top: 5px; width: 80%;'> </label></td>"+
                          "</tr>";

		    	$('#culot_urinaire_ligne_'+(nbLigne-1)).after(html);
		    	
		    	if(nbLigne == 9){
		    		$('#culot_urinaire_plus').toggle(false);
		    	}
	    	}

	    });
    	
    	$('#culot_urinaire_moins').click(function(){ 
	    	var nbLigne = $("#culot_urinaire_tableau tr").length;
	    	
	    	if(nbLigne > 2){
		    	$('#culot_urinaire_ligne_'+(nbLigne-1)).remove();
		    	if(nbLigne == 3){ 
		    		$('#culot_urinaire_moins').toggle(false);
		    	}
		    	
		    	if(nbLigne == 10){
		    		$('#culot_urinaire_plus').toggle(true);
		    	}
	    	}

	    });
    	
    }
    
    function getCulotUrinaireListeSelect(){
    	var tab = [];
    	var nbLigne = $("#culot_urinaire_tableau tr").length;
    	var j = 1;
    	
    	tab[0] = $('#type_materiel_culot_urinaire').val();
    	tab[1] = new Array(); 
    	tab[2] = new Array(); 
    	for(var i=1 ; i<nbLigne ; i++){
    		var listeSelect1  = $('#culot_urinaire_ligne_'+i+' .listeSelect select' ).val();
    		var listeSelect2 = $('#culot_urinaire_ligne_'+i+' .emplaceListeElemtsCUSelect select').val();
    		tab[1][j]   = listeSelect1;
    		if(listeSelect2){ 
    			tab[2][j++] = listeSelect2;
    		}else{
    			tab[2][j++] = null;
    		}
    	}
	    tab[3] = $('#conclusion_culot_urinaire_valeur').val();
	    
	    return tab;
    }
    // FIN DE GESTION DE L'ANALYSE Culot_urinaire 
    // FIN DE GESTION DE L'ANALYSE Culot_urinaire 
    // FIN DE GESTION DE L'ANALYSE Culot_urinaire 
    
    /*
    function getCulotUrinaire(){
    	var tab = [];
    	tab[1] = $('#type_materiel_culot_urinaire').val();
    	tab[2] = $('#culot_urinaire_1').val();
    	tab[3] = $('#culot_urinaire_2').val();
	    
	    return tab;
    }
    */
    
    function getSerologieChlamydiae(){
    	var tab = [];
    	tab[1] = $('#type_materiel_serologie_chlamydiae').val();
    	tab[2] = $('#serologie_chlamydiae').val();
	    
	    return tab;
    }
    
    function getSerologieSyphilitique(){
    	var tab = [];
    	tab[1] = $('#type_materiel_serologie_syphilitique').val();
    	tab[2] = $('#serologie_syphilitique').val();
	    
	    return tab;
    }
    
    function getAslo(){
    	var tab = [];
    	tab[1] = $('#type_materiel_aslo').val();
    	tab[2] = $('#aslo').val();
	    
	    return tab;
    }
    
    function getWidal(){
    	var tab = [];
    	tab[1]  = $('#type_materiel_widal').val();
    	
    	tab[2]  = $('#widal_to').val();
    	tab[3]  = $('#widal_titre_to').val();
    	
    	tab[4]  = $('#widal_th').val();
    	tab[5]  = $('#widal_titre_th').val();
    	
    	tab[6]  = $('#widal_ao').val();
    	tab[7]  = $('#widal_titre_ao').val();
    	
    	tab[8]  = $('#widal_ah').val();
    	tab[9]  = $('#widal_titre_ah').val();
    	
    	tab[10] = $('#widal_bo').val();
    	tab[11] = $('#widal_titre_bo').val();
	    
    	tab[12] = $('#widal_bh').val();
    	tab[13] = $('#widal_titre_bh').val();
    	
    	tab[14] = $('#widal_co').val();
    	tab[15] = $('#widal_titre_co').val();
    	
    	tab[16] = $('#widal_ch').val();
    	tab[17] = $('#widal_titre_ch').val();
    	
	    return tab;
    }
    
    function getAgHbs(){
    	var tab = [];
    	tab[1] = $('#type_materiel_ag_hbs').val();
    	tab[2] = $('#ag_hbs').val();
	    
	    return tab;
    }
    
    //Automatisation des champs calculables  -----  Automatisation des champs calculables
    //Automatisation des champs calculables  -----  Automatisation des champs calculables
    //Automatisation des champs calculables  -----  Automatisation des champs calculables
    function rapportCHOL_HDL(){
    	var cholesterol_total_1 = $('#cholesterol_total_1').val();
    	var cholesterol_HDL_1 = $('#cholesterol_HDL_1').val();
    	var rapport = null;
    	
    	if(cholesterol_total_1 && cholesterol_HDL_1){
    		rapport = cholesterol_total_1/cholesterol_HDL_1;
    		
    		$('.rapport_chol_hdl').toggle(true);
    		$('#rapport_chol_hdl').val(rapport.toFixed(2));
    		
    		//Affichage de la conclusion du rapport
			if(rapport >= 3.5 && rapport <= 5){
				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne faible </span>');
			}else if(rapport > 5 && rapport <= 6.5){
				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne mod&eacute;r&eacute; </span>');
			}else if(rapport > 6.5){
				$('#conclusion_rapport_chol_hdl').html('<span style="color: red; float: left"> Risque d\'ath&eacute;rog&egrave;ne &eacute;lev&eacute; </span>');
			}else{
				$('#conclusion_rapport_chol_hdl').html('<span style="color: green; float: left;"> RAS </span>');
			}
    	}
    	
    	$("#cholesterol_total_1, #cholesterol_HDL_1").keyup( function () {
    		var cholesterol_total_1 = $("#cholesterol_total_1").val();
    		var cholesterol_HDL_1 = $("#cholesterol_HDL_1").val();
    		
    		if( cholesterol_total_1 == "" || cholesterol_total_1 == 0 || cholesterol_HDL_1 == "" || cholesterol_HDL_1 == 0 ){
    			$('.rapport_chol_hdl table').toggle(false);
    		}else
    		if( cholesterol_total_1 && cholesterol_HDL_1 ){
    			var rapport = cholesterol_total_1/cholesterol_HDL_1;
    			$('.rapport_chol_hdl').toggle(true);
    			$("#rapport_chol_hdl").val(rapport.toFixed(2));
    			$('.rapport_chol_hdl table').toggle(true);
    			
    			//Affichage de la conclusion du rapport
    			if(rapport >= 3.5 && rapport <= 5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne faible </span>');
    			}else if(rapport > 5 && rapport <= 6.5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne mod&eacute;r&eacute; </span>');
    			}else if(rapport > 6.5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: red; float: left"> Risque d\'ath&eacute;rog&egrave;ne &eacute;lev&eacute; </span>');
    			}else{
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: green; float: left;"> RAS </span>');
    			}
    		}
    		else { 
    			$("#rapport_chol_hdl").val(null); 
    			$('#conclusion_rapport_chol_hdl').html('<span style="color: green; float: left;"> RAS </span>');	
    		}
    		
    	} ).change( function () {
    		var cholesterol_total_1 = $("#cholesterol_total_1").val();
    		var cholesterol_HDL_1 = $("#cholesterol_HDL_1").val();
    		
    		if( cholesterol_total_1 == "" || cholesterol_total_1 == 0 || cholesterol_HDL_1 == "" || cholesterol_HDL_1 == 0 ){
    			$('.rapport_chol_hdl table').toggle(false);
    		}else
    		if( cholesterol_total_1 && cholesterol_HDL_1 ){
    			var rapport = cholesterol_total_1/cholesterol_HDL_1;
    			$('.rapport_chol_hdl').toggle(true);
    			$("#rapport_chol_hdl").val(rapport.toFixed(2));
    			$('.rapport_chol_hdl table').toggle(true);

    			//Affichage de la conclusion du rapport
    			if(rapport >= 3.5 && rapport <= 5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne faible </span>');
    			}else if(rapport > 5 && rapport <= 6.5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: orange; float: left"> Risque d\'ath&eacute;rog&egrave;ne mod&eacute;r&eacute; </span>');
    			}else if(rapport > 6.5){
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: red; float: left"> Risque d\'ath&eacute;rog&egrave;ne &eacute;lev&eacute; </span>');
    			}else{
    				$('#conclusion_rapport_chol_hdl').html('<span style="color: green; float: left;"> RAS </span>');
    			}
    			
    		}
    		else {
    			$("#rapport_chol_hdl").val(null); 
    			$('#conclusion_rapport_chol_hdl').html('<span style="color: green; float: left"> RAS </span>');	
    		}
    		
    	} );
    	
    	
    }
    
    function getCreatininemie_umol(){
    	var creatininemie = $('#creatininemie').val();
    	var valeur_umol = null;
    	if(creatininemie){
    		valeur_umol = creatininemie * 8.84;
    		$('#creatininemie_umol').val(valeur_umol.toFixed(2));
    	}else{
    		$('#creatininemie_umol').val(null);
    	}
    	
    	$('#creatininemie').keyup( function () {
    		creatininemie = $('#creatininemie').val();
    		if(creatininemie){
        		valeur_umol = creatininemie * 8.84;
        		$('#creatininemie_umol').val(valeur_umol.toFixed(2));
        	}else{
        		$('#creatininemie_umol').val(null);
        	}
    	}).change( function(){
    		creatininemie = $('#creatininemie').val();
    		if(creatininemie){
        		valeur_umol = creatininemie * 8.84;
        		$('#creatininemie_umol').val(valeur_umol.toFixed(2));
        	}else{
        		$('#creatininemie_umol').val(null);
        	}
    	});
    	
    }
    
    function getTcaRatio(){
    	var tca_patient = $('#tca_patient').val();
    	var temoin_patient = $('#temoin_patient').val();
    	
    	if(tca_patient && temoin_patient){
    		var tca_ratio = tca_patient/temoin_patient;
    		$('#tca_ratio').val(tca_ratio.toFixed(2));
    	}else{
    		$('#tca_ratio').val(null);
    	}
    	
    	$('#tca_patient, #temoin_patient').keyup( function () {
    		var tca_patient = $('#tca_patient').val();
        	var temoin_patient = $('#temoin_patient').val();
        	
    		if(tca_patient && temoin_patient){
        		var tca_ratio = tca_patient/temoin_patient;
        		$('#tca_ratio').val(tca_ratio.toFixed(2));
        	}else{
        		$('#tca_ratio').val(null);
        	}
    		
    	}).change( function(){
    		var tca_patient = $('#tca_patient').val();
        	var temoin_patient = $('#temoin_patient').val();
        	
    		if(tca_patient && temoin_patient){
        		var tca_ratio = tca_patient/temoin_patient;
        		$('#tca_ratio').val(tca_ratio.toFixed(2));
        	}else{
        		$('#tca_ratio').val(null);
        	}
    	});
    	
    }
    
    function getCholesterolTotal(){
    	var cholesterol_total_1 = $('#cholesterol_total_1').val();
    	var valeur_mmol = null;
    	
    	$('#cholesterol_total_1').keyup( function () {
    		cholesterol_total_1 = $('#cholesterol_total_1').val();
    		if(cholesterol_total_1){
        		valeur_mmol = cholesterol_total_1 * 2.587;
        		$('#cholesterol_total_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_total_2').val(null);
        	}
    	}).change( function(){
    		cholesterol_total_1 = $('#cholesterol_total_1').val();
    		if(cholesterol_total_1){
        		valeur_mmol = cholesterol_total_1 * 2.587;
        		$('#cholesterol_total_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_total_2').val(null);
        	}
    	});
    	
    }
    
    function getCholesterolHDL(){
    	var cholesterol_HDL_1 = $('#cholesterol_HDL_1').val();
    	var valeur_mmol = null;
    	
    	$('#cholesterol_HDL_1').keyup( function () {
    		cholesterol_HDL_1 = $('#cholesterol_HDL_1').val();
    		if(cholesterol_HDL_1){
        		valeur_mmol = cholesterol_HDL_1 * 2.587;
        		$('#cholesterol_HDL_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_HDL_2').val(null);
        	}
    	}).change( function(){
    		cholesterol_HDL_1 = $('#cholesterol_HDL_1').val();
    		if(cholesterol_HDL_1){
        		valeur_mmol = cholesterol_HDL_1 * 2.587;
        		$('#cholesterol_HDL_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_HDL_2').val(null);
        	}
    	});
    	
    }
    
    function getCholesterolLDL(){
    	var cholesterol_LDL_1 = $('#cholesterol_LDL_1').val();
    	var valeur_mmol = null;
    	
    	$('#cholesterol_LDL_1').keyup( function () {
    		cholesterol_LDL_1 = $('#cholesterol_LDL_1').val();
    		if(cholesterol_LDL_1){
        		valeur_mmol = cholesterol_LDL_1 * 2.587;
        		$('#cholesterol_LDL_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_LDL_2').val(null);
        	}
    	}).change( function(){
    		cholesterol_LDL_1 = $('#cholesterol_LDL_1').val();
    		if(cholesterol_LDL_1){
        		valeur_mmol = cholesterol_LDL_1 * 2.587;
        		$('#cholesterol_LDL_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#cholesterol_LDL_2').val(null);
        	}
    	});
    	
    }
    

    function getTriglycerides(){
    	var triglycerides_1 = $('#triglycerides_1').val();
    	var valeur_mmol = null;
    	
    	$('#triglycerides_1').keyup( function () {
    		triglycerides_1 = $('#triglycerides_1').val();
    		if(triglycerides_1){
        		valeur_mmol = triglycerides_1 * 1.143;
        		$('#triglycerides_2').val(valeur_mmol.toFixed(3));
        	}else{
        		$('#triglycerides_2').val(null);
        	}
    	}).change( function(){
    		triglycerides_1 = $('#triglycerides_1').val();
    		if(triglycerides_1){
        		valeur_mmol = triglycerides_1 * 1.143;
        		$('#triglycerides_2').val(valeur_mmol.toFixed(3));
        	}else{
        		$('#triglycerides_2').val(null);
        	}
    	});
    	
    }
    
    
    function getGlycemieFormule(){
    	var glycemie_1 = $('#glycemie_1').val();
    	var valeur_mmol = null;
    	
    	$('#glycemie_1').keyup( function () {
    		glycemie_1 = $('#glycemie_1').val();
    		if(glycemie_1){
        		valeur_mmol = glycemie_1 * 5.55;
        		$('#glycemie_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#glycemie_2').val(null);
        	}
    	}).change( function(){
    		glycemie_1 = $('#glycemie_1').val();
    		if(glycemie_1){
        		valeur_mmol = glycemie_1 * 5.55;
        		$('#glycemie_2').val(valeur_mmol.toFixed(2));
        	}else{
        		$('#glycemie_2').val(null);
        	}
    	});
    	
    }
    
    
    function getElectrophoreseProteinesFormule(){
    	var albumine = $('#albumine').val();
    	var alpha_1  = $('#alpha_1').val();
    	var alpha_2  = $('#alpha_2').val();
    	var beta_1   = $('#beta_1').val();
    	var beta_2   = $('#beta_2').val();
    	var gamma    = $('#gamma').val();
    	var proteine_totale    = $('#proteine_totale').val();
    	
    	$('#albumine, #proteine_totale').keyup( function () {
    		albumine = $('#albumine').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(albumine && proteine_totale){ 
        		var albumine_abs = (albumine * proteine_totale)/100;
        		$('#albumine_abs').val(albumine_abs.toFixed(1));
        	}else{
        		$('#albumine_abs').val(null);
        	}
    	}).change( function(){
    		albumine = $('#albumine').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(albumine && proteine_totale){ 
        		var albumine_abs = (albumine * proteine_totale)/100;
        		$('#albumine_abs').val(albumine_abs.toFixed(1));
        	}else{
        		$('#albumine_abs').val(null);
        	}
    	});
    	
    	$('#alpha_1, #proteine_totale').keyup( function () {
    		alpha_1 = $('#alpha_1').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(alpha_1 && proteine_totale){ 
        		var alpha_1_abs = (alpha_1 * proteine_totale)/100;
        		$('#alpha_1_abs').val(alpha_1_abs.toFixed(1));
        	}else{
        		$('#alpha_1_abs').val(null);
        	}
    	}).change( function(){
    		alpha_1 = $('#alpha_1').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(alpha_1 && proteine_totale){ 
        		var alpha_1_abs = (alpha_1 * proteine_totale)/100;
        		$('#alpha_1_abs').val(alpha_1_abs.toFixed(1));
        	}else{
        		$('#alpha_1_abs').val(null);
        	}
    	});
    	
    	$('#alpha_2, #proteine_totale').keyup( function () {
    		alpha_2 = $('#alpha_2').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(alpha_2 && proteine_totale){ 
        		var alpha_2_abs = (alpha_2 * proteine_totale)/100;
        		$('#alpha_2_abs').val(alpha_2_abs.toFixed(1));
        	}else{
        		$('#alpha_2_abs').val(null);
        	}
    	}).change( function(){
    		alpha_2 = $('#alpha_2').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(alpha_2 && proteine_totale){ 
        		var alpha_2_abs = (alpha_2 * proteine_totale)/100;
        		$('#alpha_2_abs').val(alpha_2_abs.toFixed(1));
        	}else{
        		$('#alpha_2_abs').val(null);
        	}
    	});
    	
    	$('#beta_1, #proteine_totale').keyup( function () {
    		beta_1 = $('#beta_1').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(beta_1 && proteine_totale){ 
        		var beta_1_abs = (beta_1 * proteine_totale)/100;
        		$('#beta_1_abs').val(beta_1_abs.toFixed(1));
        	}else{
        		$('#beta_1_abs').val(null);
        	}
    	}).change( function(){
    		beta_1 = $('#beta_1').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(beta_1 && proteine_totale){ 
        		var beta_1_abs = (beta_1 * proteine_totale)/100;
        		$('#beta_1_abs').val(beta_1_abs.toFixed(1));
        	}else{
        		$('#beta_1_abs').val(null);
        	}
    	});
    	
    	$('#beta_2, #proteine_totale').keyup( function () {
    		beta_2 = $('#beta_2').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(beta_2 && proteine_totale){ 
        		var beta_2_abs = (beta_2 * proteine_totale)/100;
        		$('#beta_2_abs').val(beta_2_abs.toFixed(1));
        	}else{
        		$('#beta_2_abs').val(null);
        	}
    	}).change( function(){
    		beta_2 = $('#beta_2').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(beta_2 && proteine_totale){ 
        		var beta_2_abs = (beta_2 * proteine_totale)/100;
        		$('#beta_2_abs').val(beta_2_abs.toFixed(1));
        	}else{
        		$('#beta_2_abs').val(null);
        	}
    	});
    	
    	$('#gamma, #proteine_totale').keyup( function () {
    		gamma = $('#gamma').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(gamma && proteine_totale){ 
        		var gamma_abs = (gamma * proteine_totale)/100;
        		$('#gamma_abs').val(gamma_abs.toFixed(1));
        	}else{
        		$('#gamma_abs').val(null);
        	}
    	}).change( function(){
    		gamma = $('#gamma').val();
    		proteine_totale    = $('#proteine_totale').val();
    		if(gamma && proteine_totale){ 
        		var gamma_abs = (gamma * proteine_totale)/100;
        		$('#gamma_abs').val(gamma_abs.toFixed(1));
        	}else{
        		$('#gamma_abs').val(null);
        	}
    	});
    }
    
    function getAsatAlatAuto(){
    	$('#type_materiel_tgo_asat').keyup( function () {
    		var type_materiel_tgo_asat = $('#type_materiel_tgo_asat').val();
    		
    		if(type_materiel_tgo_asat){ 
        		$('#type_materiel_tgp_alat').val(type_materiel_tgo_asat);
        	}else{
        		$('#type_materiel_tgp_alat').val(null);
        	}
    	}).change( function(){
    		var type_materiel_tgo_asat = $('#type_materiel_tgo_asat').val();
    		
    		if(type_materiel_tgo_asat){ 
        		$('#type_materiel_tgp_alat').val(type_materiel_tgo_asat);
        	}else{
        		$('#type_materiel_tgp_alat').val(null);
        	}
    	});
    }
    
    function getAlbumineUrinaireVal(resultat){
    	
    	if(resultat == 'positif'){
    		$('#albumine_urinaire_degres').fadeIn(500);
    	}else{
    		$('#albumine_urinaire_degres').toggle(false);
    	}
    	
    }
    
    function getSucreUrinaireVal(resultat){
    	
    	if(resultat == 'positif'){
    		$('#sucre_urinaire_degres').fadeIn(500);
    	}else{
    		$('#sucre_urinaire_degres').toggle(false);
    	}
    	
    }
    
    function getCorpsCetoniqueUrinaireVal(resultat){
    	
    	if(resultat == 'positif'){
    		$('#corps_cetonique_urinaire_degres').fadeIn(500);
    	}else{
    		$('#corps_cetonique_urinaire_degres').toggle(false);
    	}
    	
    }
    
    function getChampFloreNote(id){
    	if(id == ''){
    		$('.flore_note_class_pv').css({'visibility':'hidden'});
    	}else{
    		$('.flore_note_class_pv').css({'visibility':'visible'}).val('');
    	}
    }
    
    function getChampIdentificationRdmPositive(id){
    	if(id == '' || id == 2){
    		$('.identification_rdm_positive_class_pv').css({'visibility':'hidden'});
    	}else{
    		$('.identification_rdm_positive_class_pv').css({'visibility':'visible'}).val('');
    	}
    }
    
    function getIconeAntibiogrammeIdentCulture(id,i){ 
    	if(id == 0){
    		$('.antibiogrammeButtonAffInterface'+i).toggle(false);
    	}else{
    		$('.antibiogrammeButtonAffInterface'+i).toggle(true);
    	}
    }
    
    
    function getListeIdentificationCulture(id){
    	
   	 var listeIdentificationCulture = new Array();
   	 listeIdentificationCulture[1]='Candida albicans';
   	 listeIdentificationCulture[2]='Escherichia coli';
   	 listeIdentificationCulture[3]='Staphylococcus aureus';
   	 
   	 return listeIdentificationCulture[id];
   }
   
  
    //Affichage de l'interface antibiogramme
   function antibiogrammeAfficherInterface(){
   	
   	$( "#resultatsAnalysesPVAntiBioGramme" ).dialog({
   		resizable: false,
   		height:680,
   		width:800,
   		autoOpen: false,
   		modal: true,
   		buttons: { 			

   			"Terminer": function() {
   				$(this).dialog( "close" );
   			}
   		}
   	});
   	
   	var id = $('#identification_culture_pv').val();
   	
   	$("#valeurSoucheIsoleeIdentificationCulturePV").html(getListeIdentificationCulture(id));
   	$("#resultatsAnalysesPVAntiBioGramme").dialog('open');
   	
   }
   
   function getNombreCultureIdentifier(id){
   	if(id==1){
       	$('.nombreCultureIdentifierABG').css('visibility','visible').val(1);
       	$('.identificationCultureChampsABR_1').toggle(true);
   	}else{
       	$('.nombreCultureIdentifierABG').css('visibility','hidden');
       	$('.identificationCultureChamps').toggle(false);
   	}
   }
   
   function getChampIdentificationRdmPositive(id){
	   
	   	if(id == '' || id == 2){
	   		$('.identification_rdm_positive_class_titre_pv, .identification_rdm_positive_class_pv').toggle(false);
	   	}else{
	   		$('.identification_rdm_positive_class_titre_pv, .identification_rdm_positive_class_pv').toggle(true);
	   	}
   }
	   
   function getAutreFloreCocciPositif(id){

	   if(id==3){ 
	   		$(".autre_flore_cocci_positif_pv").css({'visibility':'visible'});
	   	}else{
	   		$(".autre_flore_cocci_positif_pv").css({'visibility':'hidden'});
	   	}
   }
   
   function getChampIdentificationCulture(nb){ 
   	
   	for(var i=1 ; i<nb ; i++){ 
   		var champIdentCult =""+
       	
			  '<table style="width: 100%;" class="identificationCultureChamps champIdentCultABG identificationCultureChampsABR_'+(i+1)+'"  style="visibility:hidden;">'+
		      '<tr class="ligneAnanlyse" style="width: 100%;">'+
		      '  <td style="width: 25%;"><label class="lab1" style="padding-top: 5px;"><span style="font-weight: bold;"> Identification </span></label></td>'+
		      '  <td style="width: 27%;">'+
		      '              <label class="lab2" style="padding-top: 5px;">'+
		      '                <select id="identification_culture_pv" style="width: 190px;" onchange="getIconeAntibiogrammeIdentCulture(this.value,'+(i+1)+')">'+
		      '                 <option value=0 > </option>'+
		      '                  <option value=1 >Candida albicans</option>'+
		      '                  <option value=2 >Escherichia coli</option>'+
		      '                  <option value=3 >Staphylococcus aureus</option>'+
		      '                </select>'+
		      '              </label>'+
		      '          </td>'+
		      '  <td style="width: 48%;"><label class="lab1 antibiogrammeButtonAffInterface'+(i+1)+'" style="padding-top: 0px; margin-top: 3px; margin-left: 10px; width: 30%; height: 15px; font-style: italic; border-radius: 35%; border: 3px solid #d8d8d8; padding-left: 10px; display: none;"> Antibiogramme </label></td>'+
		      '</tr>'+
		      '</table>';
   		
   		$('.identificationCultureChampsABR_'+i).after(champIdentCult);
   		
   	}
   	
   }
   
   function getChampsCultureIdentifierABG(id){ 
   	$(".champIdentCultABG").remove();
   	getChampIdentificationCulture(id);
   }
    
    /**
     * ************************************************
     * ------------------------------------------------
     * ************************************************
     * ------------------------------------------------
     */
    
    //Resultats des analyses d'une seule demande
    //Resultats des analyses d'une seule demande
    //Resultats des analyses d'une seule demande
    function resultatsDesAnalysesDeLaDemande(iddemande, tabAnalyses, tabDemandes){
  	    $( "#resultatsAnalysesDuneDemande" ).dialog({
  	    resizable: false,
  	    height:670,
  	    width:750,
  	    autoOpen: false,
  	    modal: true,
  	    buttons: {
//  	    	"Enregistrer": function() {
//  	          
//  	    		var tab = []; 
//  	    		for(var i = 0 ;  i<tabAnalyses.length ; i++){
//  	    			var idanalyse = tabAnalyses[i];
//  	    			
//  	    			     if(idanalyse ==  1) { tab  [1] = getChampsNfs(); }
//    				else if(idanalyse ==  2) { tab  [2] = getChampsGroupeRhesus(); }
//    				else if(idanalyse ==  3) { tab  [3] = new Array("", $('#antigene_d_faible').val()); }
//    				else if(idanalyse ==  4) { tab  [4] = testCombsDirect(); }
//    				else if(idanalyse ==  5) { tab  [5] = testCombsIndirect(); }
//       				else if(idanalyse ==  6) { tab  [6] = testCompatibilite(); }
//       				else if(idanalyse ==  7) { tab  [7] = new Array("", $('#vitesse_sedimentation').val()); }
//    				else if(idanalyse ==  8) { tab  [8] = new Array("", $('#test_demmel').val()); }
//    				else if(idanalyse ==  9) { tab  [9] = new Array("", $('#taux_reticulocyte').val()); }
//    				else if(idanalyse == 10) { tab [10] = getGoutteEpaisse(); }
//  	    			     
//    				else if(idanalyse == 14) { tab [14] = getTpInr(); }
//    				else if(idanalyse == 15) { tab [15] = getTca();  }
//    				else if(idanalyse == 16) { tab [16] = new Array("", $('#fibrinemie').val());  }
//    				else if(idanalyse == 17) { tab [17] = new Array("", $('#temps_saignement').val());  }
//  	    			    
//    				else if(idanalyse == 21) { tab [21] = getGlycemie(); }
//    				else if(idanalyse == 22) { tab [22] = new Array("", $('#creatininemie').val()); }
//    				else if(idanalyse == 23) { tab [23] = new Array("", $('#uree_sanguine').val()); }
//    				else if(idanalyse == 24) { tab [24] = new Array("", $('#acide_urique').val()); }
//    				else if(idanalyse == 25) { tab [25] = getCholesterolTotal(); }
//    				else if(idanalyse == 26) { tab [26] = getTriglycerides(); }
//    				else if(idanalyse == 27) { tab [27] = getCholesterolHDL(); }
//    				else if(idanalyse == 28) { tab [28] = getCholesterolLDL(); }
//    				else if(idanalyse == 29) { tab [29] = getChol_Total_HDL_LDL_Trigly(); }
//    				else if(idanalyse == 30) { tab [30] = getLipidesTotaux(); } 
//    			    else if(idanalyse == 31) { tab [31] = getIonogramme(); }
//    				else if(idanalyse == 32) { tab [32] = new Array("", $('#calcemie').val()); } 
//    				else if(idanalyse == 33) { tab [33] = new Array("", $('#magnesemie').val()); }
//    				else if(idanalyse == 34) { tab [34] = new Array("", $('#phosphoremie').val()); }
//    				else if(idanalyse == 35) { tab [35] = getAsat(); }
//    				else if(idanalyse == 36) { tab [36] = getAlat(); }
//    				else if(idanalyse == 37) { tab [37] = getAsatAlat(); }
//    				else if(idanalyse == 38) { tab [38] = new Array("", $('#phosphatage_alcaline').val()); }
//    				else if(idanalyse == 39) { tab [39] = new Array("", $('#gama_gt').val()); }
//    				else if(idanalyse == 40) { tab [40] = getFerSerique(); }   
//    				else if(idanalyse == 41) { tab [41] = getFerritinine(); } 
//    				else if(idanalyse == 42) { tab [42] = getBilirubineTotaleDirecte(); } 
//    				else if(idanalyse == 43) { tab [43] = getHemoglobineGlyqueeHBAC(); } 
//    				else if(idanalyse == 44) { tab [44] = getElectrophoreseHemoglobine(); }     
//    				else if(idanalyse == 45) { tab [45] = getElectrophoreseProteines(); }     
//    				else if(idanalyse == 46) { tab [46] = getAlbuminemie(); }    
//    				else if(idanalyse == 47) { tab [47] = getAlbumineUrinaire(); } 
//    				else if(idanalyse == 48) { tab [48] = getProtidemie(); } 
//    				else if(idanalyse == 49) { tab [49] = getProteinurie(); } 
//    				else if(idanalyse == 50) { tab [50] = getHlmCompteDaddis(); } 
//    				else if(idanalyse == 51) { tab [51] = getBetaHcgPlasmatique(); } 
//    				else if(idanalyse == 52) { tab [52] = getPsa(); } 
//    				else if(idanalyse == 53) { tab [53] = getCrp(); } 
//    				else if(idanalyse == 54) { tab [54] = getFacteursRhumatoides(); } 
//    				else if(idanalyse == 55) { tab [55] = getRfWaalerRose(); } 
//    				else if(idanalyse == 56) { tab [56] = getToxoplasmose(); } 
//    				else if(idanalyse == 57) { tab [57] = getRubeole(); }
//    				else if(idanalyse == 58) { tab [58] = getCulotUrinaire(); } 
//    				else if(idanalyse == 59) { tab [59] = getSerologieChlamydiae(); } 
//    				else if(idanalyse == 60) { tab [60] = getSerologieSyphilitique(); } 
//    				else if(idanalyse == 61) { tab [61] = getAslo(); } 
//    				else if(idanalyse == 62) { tab [62] = getWidal(); } 
//    				else if(idanalyse == 63) { tab [63] = getAgHbs(); } 
//  	    			     
//  	    			     
//  	    			     
//    				else if(idanalyse == 68) { tab [68] = getTypageHemoglobine(); }
//  	    		}
//  	    		
//  	    		
//  	        	$( this ).dialog( "close" );
//  	            
//  	            $.ajax({
//  	                type: 'POST',
//  	                url: tabUrl[0]+'public/biologiste/enregistrer-resultats-demande',
//  	                data:{'tabAnalyses':tabAnalyses, 'tabDemandes':tabDemandes, 'tab':tab},
//  	                success: function(data) {
//  	                	     var iddemande = jQuery.parseJSON(data);
//  	                	     $('.visualiser'+iddemande+' img').trigger('click');
//  	                }
//  	            });
//  	        	
//  	        },
  	        
  	        "Terminer": function() {
  	        	$(this).dialog( "close" );
  	        }
  	   }
  	  });
    }
    
    
    function resultatsDesAnalyses(iddemande){
    	var typeResultat = $('#typeResultat').val(); 
    	
        var chemin = tabUrl[0]+'public/biologiste/recuperer-les-analyses-de-la-demande';
        $.ajax({
            type: 'POST',
            url: chemin ,
            data: {'iddemande':iddemande, 'typeResultat':typeResultat },
            success: function(data) {
            	     var result = jQuery.parseJSON(data); 

            	     var html = result[0];
            	     var tabAnalyses = result[1];
            	     var tabDemandes = result[2];
            	     resultatsDesAnalysesDeLaDemande(iddemande, tabAnalyses, tabDemandes);
            	     
            	     $('#contenuResultatsAnalysesParType div').empty();
            	     $('#contenuResultatsAnalyses div').empty();
               	     $('#contenuResultatsAnalysesDuneDemande div').empty();
            	     $('#contenuResultatsAnalysesDuneDemande div').html(html);
            	     gestionFormuleLeucocytaire();
            	     rapportCHOL_HDL();
            	     getCreatininemie_umol();
            	     getTcaRatio();
            	     getCholesterolHDL();
            	     getCholesterolLDL();
            	     getCholesterolTotal();
            	     getTriglycerides();
            	     getGlycemieFormule();
            	     getElectrophoreseProteinesFormule();
            	     getTestCombsDirect();
            	     getTestCombsIndirect();
            	     getTestCompatibilite();
            	     getAsatAlatAuto();
            	     getFerSeriqueFormule();
            	     ajoutCulotUrinaireAuto();
            	     ajoutCulotEcbuAuto();
            	     
            	     $("#resultatsAnalysesDuneDemande").dialog('open');
            	     
            	     //Ajouter des lignes
                 	 //Ajouter des lignes
           	         getTestCombsIndirectAjout();
           	         getElectroHemo();
                 	
           	         
            	     
                 	 //Bloquer la saisie sur tous les champs de commentaires
                  	 //Bloquer la saisie sur tous les champs de commentaires
                     $('#commentaire_hemogramme, #commentaire_pv, #conclusion_pv_ABG, #conclusion_ecbu_ABG, #autres_precisions_commentaire_pv, #autres_precisions_commentaire_ecbu').attr('readonly', true);
                  	 $('.commentaire_protect textarea').attr('readonly', true);
                  	 $('#idCommentaireChoiceCheckPV').toggle(false);
                 	
            }
        });
    }
    
    /**
     * AJOUTER DE PLUSIEURS RESULTAT PAR LES '+' & '-'
     * AJOUTER DE PLUSIEURS RESULTAT PAR LES '+' & '-'
     * AJOUTER DE PLUSIEURS RESULTAT PAR LES '+' & '-'
     */
    function getTestCombsIndirectAjout(){ 
    	
    	$('#test_combs_indirect_moins').toggle(false);
	    
    	$('#test_combs_indirect_plus').click(function(){
	    	var nbLigne = $("#test_combs_rai tr").length;
	    	$('#test_combs_indirect_moins').toggle(true);
	    	
	    	if(nbLigne < 10){
	    		var html ="<tr id='test_combs_rai_"+nbLigne+"' class='ligneAnanlyse' style='width: 100%;'>"+
                            
                            "<td style='width: 30%;'><label class='lab1' ><span style='font-weight: bold;'> RAI <select id='test_combs_indirect_"+nbLigne+"' > <option >  </option> <option value='Positif' >Positif</option> <option value='Negatif' >N&eacute;gatif</option> </select></span></label></td>"+
                    	    "<td style='width: 25%;'><label class='lab2' style='padding-top: 5px; text-align: right; '>  Titre <input id='titre_combs_indirect_"+nbLigne+"' type='text'> </label></td>"+
                    	    "<td style='width: 45%;'><label class='lab3' style='padding-top: 5px; width: 80%; padding-left: 25px;'> Temp&eacute;rature <input id='titre_combs_temperature_"+nbLigne+"' type='number' > </label></td>"+
                            
                          "</tr>";

		    	$('#test_combs_rai_'+(nbLigne-1)).after(html);
		    	$('#test_combs_indirect_'+nbLigne).val($('#test_combs_indirect_'+(nbLigne-1)).val());
		    	
		    	if(nbLigne == 9){
		    		$('#test_combs_indirect_plus').toggle(false);
		    	}
		    	
		    	//Blocage du champ titre lorsque la valeur est n�gative
			    $('#test_combs_indirect_'+nbLigne).attr('onchange', 'getTestCombsIndirectBlocTitre('+nbLigne+')');
			    if($('#test_combs_indirect_'+nbLigne).val() == 'Negatif'){ $('#test_combs_indirect_'+nbLigne).trigger('change'); }
	    	}

	    });
	    
	    $('#test_combs_indirect_moins').click(function(){ 
	    	var nbLigne = $("#test_combs_rai tr").length;
	    	
	    	if(nbLigne > 2){
		    	$('#test_combs_rai_'+(nbLigne-1)).remove();
		    	if(nbLigne == 3){ 
		    		$('#test_combs_indirect_moins').toggle(false);
		    	}
		    	
		    	if(nbLigne == 10){
		    		$('#test_combs_indirect_plus').toggle(true);
		    	}
	    	}

	    });
	    
	    
    }
    

    function testCombsIndirect(){
    	var tab = [];
		tab[1] = $('#commentaire_test_combs_indirect').val();
    	
    	return tab;
    }
    

    function getTestCombsIndirectBlocTitre(nbLigne){
    	
    	var val = $('#test_combs_indirect_'+nbLigne).val();
    	
    	if(val == 'Negatif'){
    		$('#titre_combs_indirect_'+nbLigne).val('').attr('readonly',true);
    	}else{
    		$('#titre_combs_indirect_'+nbLigne).attr('readonly',false);
    	}
    }
    
    